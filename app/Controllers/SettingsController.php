<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AgentKey;
use App\Models\User;

final class SettingsController
{
    public function index(): void
    {
        $apiKeys = (new AgentKey())->all();
        View::render('settings/index', [
            'apiKeys' => $apiKeys,
            'csrf'    => Csrf::token(),
            'user'    => Auth::user(),
            'success' => Request::input('success'),
            'error'   => Request::input('error'),
        ]);
    }

    public function updatePassword(): void // should i prohibit 12345678?.. Pfft naaah 
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/settings.php?error=csrf');
        }

        $current = (string) Request::input('current_password');
        $new     = (string) Request::input('new_password');
        $confirm = (string) Request::input('confirm_password');

        $userId = (int) (Auth::user()['id'] ?? 0);
        $user   = (new User())->findById($userId);

        if (!$user || !password_verify($current, $user['password_hash'])) {
            Response::redirect('/settings.php?error=wrong_password');
        }
        if (strlen($new) < 8) {
            Response::redirect('/settings.php?error=too_short');
        }
        if ($new !== $confirm) {
            Response::redirect('/settings.php?error=mismatch');
        }

        (new User())->updatePassword($userId, $new);
        Response::redirect('/settings.php?success=password_changed');
    }
    public function createApiKey(): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/settings.php?error=csrf');
        }
        $label = trim((string) Request::input('label', 'New key'));
        $key = (new AgentKey())->create($label ?: 'New key');
        // key can only be seen once
        $_SESSION['flash_api_key'] = $key; 
        Response::redirect('/settings.php?success=key_created');
    }
    public function revokeApiKey(int $id): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/settings.php?error=csrf');
        }
        (new AgentKey())->revoke($id);
        Response::redirect('/settings.php?success=key_revoked');
    }
}
