<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Models\Notification;
use App\Models\Peer;
use App\Models\Report;
use App\Models\Router;
use App\Models\Snapshot;

final class DashboardController
{
    public function index(): void
    {
        $routers       = (new Router())->allWithSummary();
        $notifications = (new Notification())->latest();
        $unreadCount   = (new Notification())->unreadCount();
        $router        = $routers[0] ?? null;

        // Snapshots for chart (last 12 ingests)
        $snapshots = $router
            ? (new Snapshot())->latestByRouter((int) $router['id'], 12)
            : [];

        // Always show peers from the LATEST snapshot only
        $latestSnapshot = $snapshots[0] ?? null;
        $peers = $latestSnapshot
            ? (new Peer())->bySnapshot((int) $latestSnapshot['id'])
            : [];

        $reports = $router
            ? (new Report())->latestByRouter((int) $router['id'])
            : [];

        $theme = $_SESSION['theme'] ?? 'dark';
        $csrf  = Csrf::token();

        View::render('dashboard/index', compact(
            'routers', 'router', 'peers', 'snapshots', 'reports',
            'notifications', 'unreadCount', 'theme', 'csrf'
        ) + ['user' => Auth::user()]);
    }
}
