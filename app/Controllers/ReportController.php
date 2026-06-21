<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;
use App\Models\Report;
use App\Services\MarkdownRenderer;

final class ReportController
{
    public function index(): void
    {
        $reports = (new Report())->allWithRouter();
        View::render('reports/index', [
            'reports' => $reports,
            'user'    => Auth::user(),
        ]);
    }

    public function show(int $id): void
    {
        $report = (new Report())->findById($id);
        if (!$report) {
            Response::redirect('/reports.php?error=notfound');
        }
        $html = (new MarkdownRenderer())->render($report['markdown_report']);
        View::render('reports/show', [
            'report' => $report,
            'html'   => $html,
            'user'   => Auth::user(),
        ]);
    }

    public function export(int $id, string $format): void
    {
        $report = (new Report())->findById($id);
        if (!$report) {
            http_response_code(404);
            exit('Report not found');
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($report['hostname'] ?? 'report'));
        $filename = "bgpdoctor-report-{$slug}-{$id}";

        match ($format) {
            'json' => $this->exportJson($report, $filename),
            'csv'  => $this->exportCsv($report, $filename),
            default => $this->exportText($report, $filename),
        };
    }

    private function exportJson(array $report, string $filename): never // i need monster.
    {
        header('Content-Type: application/json');
        header("Content-Disposition: attachment; filename=\"{$filename}.json\"");
        echo json_encode([
            'id'         => $report['id'],
            'hostname'   => $report['hostname'],
            'asn'        => $report['asn'],
            'summary'    => $report['summary'],
            'risk_level' => $report['risk_level'],
            'report'     => $report['markdown_report'],
            'created_at' => $report['created_at'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function exportCsv(array $report, string $filename): never
    {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['id', 'hostname', 'asn', 'summary', 'risk_level', 'created_at']);
        fputcsv($handle, [
            $report['id'],
            $report['hostname'],
            $report['asn'],
            $report['summary'],
            $report['risk_level'],
            $report['created_at'],
        ]);
        fputcsv($handle, ['--- report body ---']);
        // Write report body line by line
        foreach (explode("\n", $report['markdown_report']) as $line) {
            fputcsv($handle, [$line]);
        }
        fclose($handle);
        exit;
    }

    private function exportText(array $report, string $filename): never
    {
        header('Content-Type: text/plain; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.txt\"");
        echo "BGPDoctor Report\n";
        echo str_repeat('=', 60) . "\n";
        echo "Router  : {$report['hostname']} (AS{$report['asn']})\n";
        echo "Summary : {$report['summary']}\n";
        echo "Risk    : {$report['risk_level']}\n";
        echo "Date    : {$report['created_at']}\n";
        echo str_repeat('-', 60) . "\n\n";
        echo $report['markdown_report'];
        exit;
    }
}


// genuinly what am i doing here, im doing ts while my gf is ovulating and needs attention if i die, then with honour
// ts fucked me harder than my girlfriend