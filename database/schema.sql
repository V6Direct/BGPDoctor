CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL,
    email         TEXT    NOT NULL UNIQUE,
    password_hash TEXT    NOT NULL,
    role          TEXT    NOT NULL DEFAULT 'viewer',
    created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS router_groups (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    description TEXT,
    created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS agent_api_keys (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    label        TEXT    NOT NULL,
    api_key      TEXT    NOT NULL UNIQUE,
    is_active    INTEGER NOT NULL DEFAULT 1,
    last_used_at TEXT,
    created_at   TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS routers (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    hostname     TEXT    NOT NULL UNIQUE,
    asn          INTEGER,
    group_id     INTEGER,
    software     TEXT,
    api_key_id   INTEGER,
    last_seen_at TEXT,
    created_at   TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id)   REFERENCES router_groups(id)  ON DELETE SET NULL,
    FOREIGN KEY (api_key_id) REFERENCES agent_api_keys(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS router_snapshots (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    router_id        INTEGER NOT NULL,
    raw_payload      TEXT    NOT NULL,
    cpu_percent      REAL,
    ram_percent      REAL,
    uptime_seconds   INTEGER,
    ipv4_prefixes    INTEGER,
    ipv6_prefixes    INTEGER,
    rpki_valid       INTEGER,
    rpki_invalid     INTEGER,
    peer_flaps       INTEGER DEFAULT 0,
    route_leak_score REAL    DEFAULT 0,
    latency_ms       REAL,
    load_1m          REAL,
    created_at       TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (router_id) REFERENCES routers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bgp_peers (
    id                   INTEGER PRIMARY KEY AUTOINCREMENT,
    router_id            INTEGER NOT NULL,
    snapshot_id          INTEGER NOT NULL,
    name                 TEXT    NOT NULL,
    state                TEXT    NOT NULL,
    is_ipv6              INTEGER NOT NULL DEFAULT 0,
    prefixes_received    INTEGER DEFAULT 0,
    prefixes_advertised  INTEGER DEFAULT 0,
    rpki_state           TEXT    DEFAULT 'unknown',
    latency_ms           REAL,
    flap_count           INTEGER DEFAULT 0,
    prefix_limit         INTEGER,
    pathvector_profile   TEXT,
    created_at           TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (router_id)   REFERENCES routers(id)          ON DELETE CASCADE,
    FOREIGN KEY (snapshot_id) REFERENCES router_snapshots(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ai_reports (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    router_id       INTEGER NOT NULL,
    snapshot_id     INTEGER NOT NULL,
    markdown_report TEXT    NOT NULL,
    summary         TEXT    NOT NULL,
    risk_level      TEXT    NOT NULL DEFAULT 'medium',
    created_at      TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (router_id)   REFERENCES routers(id)          ON DELETE CASCADE,
    FOREIGN KEY (snapshot_id) REFERENCES router_snapshots(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    router_id  INTEGER,
    type       TEXT    NOT NULL,
    message    TEXT    NOT NULL,
    severity   TEXT    NOT NULL DEFAULT 'info',
    is_read    INTEGER NOT NULL DEFAULT 0,
    created_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (router_id) REFERENCES routers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_preferences (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL UNIQUE,
    theme      TEXT    NOT NULL DEFAULT 'dark',
    updated_at TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
