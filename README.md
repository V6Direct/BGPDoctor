# BGPDoctor

BGP telemetry dashboard for BIRD2/pathvector routers. A lightweight Python agent runs on each router and periodically POSTs telemetry to a PHP/SQLite backend, which serves a web dashboard with AI-assisted analysis.

```
router ──[agent]──► ingest.php ──► SQLite ──► dashboard
                                       └──► analyze.php (HackClub AI)
```

## Requirements

**Agent (per router)**
- Python 3.11+
- BIRD2 with `birdc` accessible
- `ip` (iproute2), `ping` available in PATH

**Frontend**
- PHP 8.1+ with `pdo_sqlite` extension
- Any web server (nginx, Apache, Caddy)
- SQLite3

***

## Agent Setup

The agent runs on your router and ships telemetry to the backend every 60 seconds (configurable).

### 1. Install

```bash
# clone or copy the agent directory to your router
mkdir -p /etc/bgpdoctor-agent
cp agent/bgpdoctor_agent.py /etc/bgpdoctor-agent/bgpdoctor_agent.py

pip3 install -r agent/requirements.txt
# or: pip3 install aiohttp pyyaml
```

### 2. Configure

```bash
cp agent/config_example.yaml /etc/bgpdoctor-agent/config.yaml
$EDITOR /etc/bgpdoctor-agent/config.yaml
```

```yaml
backend_url: "https://bgp.example.com/api/ingest.php"
api_key: "your-api-key-here"          # must match APP_KEY in .env on the frontend
interval_seconds: 60
hostname: "edge01"                     # overrides gethostname() if set
asn: 213413
birdc_path: "/usr/sbin/birdc"
pathvector_config: "/etc/pathvector.yml"
ping_targets:
  - "1.1.1.1"
  - "2606:4700:4700::1111"
```

`ping_targets` is optional — latency values will just be null if omitted.

### 3. Test it once

```bash
python3 /etc/bgpdoctor-agent/bgpdoctor_agent.py \
  --config /etc/bgpdoctor-agent/config.yaml \
  --once
```

You should see a JSON response from the backend. If you get a 401, the `api_key` doesn't match what the frontend expects.

### 4. Install as a systemd service

```bash
cp agent/bgpdoctor-agent.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now bgpdoctor-agent
journalctl -u bgpdoctor-agent -f
```

The service runs as `root` so it can reach `birdc`. `NoNewPrivileges` and `ProtectSystem=strict` are set to limit the blast radius.

***

## Frontend Setup

The frontend is a Laravel-ish PHP app. It stores everything in SQLite so there's no database server to deal with.

### 1. Clone & configure

```bash
git clone https://github.com/V6Direct/BGPDoctor
cd BGPDoctor
cp .env.example .env
$EDITOR .env
```

Key values to change:

| Variable | What it is |
|---|---|
| `APP_URL` | Public URL of your dashboard |
| `APP_KEY` | 32-char random string — used to validate agent requests |
| `DB_PATH` | Path to the SQLite file, relative to the repo root |
| `HACKCLUB_AI_API_KEY` | HackClub AI key for the `/api/analyze.php` endpoint |
| `HACKCLUB_AI_MODEL` | Model to use (defaults to `openrouter/free`) |
| `SECURE_COOKIES` | Set `true` if serving over HTTPS |

Generate a random `APP_KEY`:
```bash
openssl rand -hex 16
```

### 2. Initialize the database

The SQLite file gets created automatically on first ingest, but you can pre-create it:

```bash
mkdir -p database
touch database/bgpdoctor.sqlite
```

Make sure the web server user (`www-data`, etc.) can write to `database/`.

```bash
chown -R www-data:www-data database/
```

### 3. Nginx config

Point the document root at `public/`. The included `.htaccess` handles Apache rewrites — for nginx use something like:

```nginx
server {
    listen 80;
    server_name bgp.example.com;
    root /var/www/BGPDoctor/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

Swap the PHP-FPM socket path for whatever your distro uses.

### 4. Agent API key

The agent sends `X-API-Key: <your key>` with every request. The backend validates it against `APP_KEY` in `.env`. Make sure both sides have the same value — that's the only auth mechanism between agent and backend.

### 5. Permissions

```bash
chmod 750 /var/www/BGPDoctor
chmod 640 /var/www/BGPDoctor/.env
chown -R www-data:www-data /var/www/BGPDoctor/database
```

Don't expose `.env` — the nginx snippet above blocks it, but double-check if you're using a different server.

***

## API endpoints

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `/api/ingest.php` | POST | `X-API-Key` header | Agent telemetry ingestion |
| `/api/analyze.php` | POST | session | AI analysis of a router snapshot |
| `/api/theme.php` | GET/POST | session | Theme preference |

***

## Multiple routers

Deploy the agent on each router with the same `backend_url` and `api_key`, but a different `hostname`. The dashboard separates routers by hostname so they all feed into the same backend.

***

## Troubleshooting

**Agent can't reach birdc**
Make sure `birdc_path` in config points to the correct binary and the agent runs as a user with permission to reach the BIRD control socket.

**401 from backend**
`api_key` in the agent config and `APP_KEY` in `.env` don't match.

**SQLite permission denied**
The web server user needs write access to both the `database/` directory and the `.sqlite` file itself.

**AI analysis returns nothing**
Check `HACKCLUB_AI_API_KEY` is set and valid. You can test it independently with a curl POST to `/api/analyze.php`.
