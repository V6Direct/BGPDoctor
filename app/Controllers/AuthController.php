<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login', ['csrf' => Csrf::token(), 'error' => null]);
    }

    public function login(): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            View::render('auth/login', ['csrf' => Csrf::token(), 'error' => 'Invalid CSRF token']);
            return;
        }

        $email      = filter_var((string) Request::input('email'), FILTER_SANITIZE_EMAIL);
        $password   = (string) Request::input('password');

        if (Auth::attempt($email, $password)) {
            Response::redirect('/');
        }

        View::render('auth/login', ['csrf' => Csrf::token(), 'error' => 'Invalid credentials']);
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login.php');
    }
}