<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Env;
use RuntimeException;

final class AIAnalyzer
{
    public function buildPrompt(array $router, array $snapshot, array $peers): string
    {
        // strip raw payload and internal DB data from th snapshot
        $health = [
            'cpu_percent' =>$snapshot['cpu_percent'] ?? null,
            'ram_percent' =>$snapshot['ram_percent'] ?? null,
            'uptime_seconds' =>$snapshot['uptime_seconds'] ?? null,
            'load_1m' =>$snapshot['load_1m'] ?? null,
            'ipv4_prefixes' =>$snapshot['ipv4_prefixes'] ?? null,
            'ipv6_prefixes' =>$snapshot['ipv6_prefixes'] ?? null,
            'rpki_valid' =>$snapshot['rpki_valid'] ?? null,
            'rpki_invalid' =>$snapshot['rpki_invalid'] ?? null,
            'peer_flaps' =>$snapshot['peer_flaps'] ?? null,
            'route_leak_score' =>$snapshot['route_leak_score'] ?? null,
            'latency_ms' =>$snapshot['latency_ms'] ?? null,
            'captured_at' =>$snapshot['caputed_at'] ?? null,
        ];

    // help.

        $cleanPeers = array_map(static function (array $p): array {
            return [
                'name'                => $p['name']                ?? 'unknown',
                'asn'                 => $p['asn']                 ?? null,
                'neighbor'            => $p['neighbor']            ?? null,
                'state'               => $p['state']               ?? 'unknown',
                'is_ipv6'             => !empty($p['is_ipv6']),
                'prefixes_received'   => (int) ($p['prefixes_received']   ?? 0),
                'prefixes_advertised' => (int) ($p['prefixes_advertised'] ?? 0),
                'rpki_state'          => $p['rpki_state']          ?? 'unknown',
                'flap_count'          => (int) ($p['flap_count']   ?? 0),
                'prefix_limit'        => isset($p['prefix_limit_explicit']) && $p['prefix_limit_explicit']
                                            ? (int) $p['prefix_limit'] : null,
                'pathvector_profile'  => $p['pathvector_profile']  ?? 'default',
            ];
        }, $peers);

    

    $data = [
        'router' => [
            'hostname' => $router['hostname'] ?? 'unknown',
            'asn' => $router['asn'] ?? null,
            'software' => $router['software'] ?? 'bird2/pathvector', //bird 1 and 2 is also supported btw :3
            'group' => $router['group_name'] ?? null,
        ],
        'health' => $health,
        'bgp_peers' => $cleanPeers,
        'peer_count' => count($cleanPeers),
        'established_count' => count(array_filter($cleanPeers, fn($p) => $p['state'] === 'Established')),
    ];

    $json = $this->prettyJson($data);

    return <<<PROMPT
    You are a senior BGP, Bird2, Bird3, Pathvector, Linux routing and network security engineer.

    Analyze the router telemetry below and produce a concise markdown diagnostic report.
    use ONLY the data provided. do NOT ISP names, ASNs, IP addresses or configuration details that are not present in the JSON. Be specific and technical.

    Focus on:
    - Routing optimization and prefix policy
    - Security concerns (route leaks, hijacks, RPKI)
    - Misconfigurations (down peers, policy errors, interface issues)
    - BGP best practices (timers, filters, communities)
    - RPKI enforcment recommendations
    - IPv6 peer and prefix policy
    - Route filtering and prefix-limit advice
    - Performance improvements
    - Peer flapping root causes
    - Route leak score analysis

    Structure your report with EXACTLY these four headers, no more:
    ## Health Summary
    ## Findings
    ## Recommendations
    ## Next Steps

    Router telemtry (JSON):
    {$json}
    PROMPT;
    }

    public function analyze(array $router, array $snapshot, array $peers): string
    {
        $apiKey = Env::get('HACKCLUB_API_KEY');
        if (!$apiKey) {
            throw new RuntimeException('Missing HACKCLUB_AI_API_KEY in enviroment');
        }

        $payload = [
            'model' => Env::get('HACKCLUB_AI_MODEL', 'openrouter/free'),
            'messages' => [
                ['role' => 'user', 'content' => $this->buildPrompt($router, $snapshot, $peers)],
            ],
        ];

        $ch = curl_init('https://ai.hackclub.com/proxy/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false || $httpCode >= 400) {
            $error = curl_error($ch) ?: 'AI API request failed';
            curl_close($ch);

            $decode = json_decode($response, true);
            return $decode['choices'][0]['message']['content'] ?? 'No analysis returned';
        }
    }
    private function prettyJson(array $data): string
        {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
        }
}