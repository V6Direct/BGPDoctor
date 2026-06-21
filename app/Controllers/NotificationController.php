<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Models\Notification;

final class NotificationController
{
    public function markRead(int $id): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::rdirect('/?error=csrf');
        }
        (new Notifications())->markRead($id);
        Response::redirect('/?success=notification_read');
    }
    public function clearAll(): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/?error=csrf');
        }
        (new Notification())->clearAll();
        Response::redirect('/?success=notifications_cleared');
    }
}