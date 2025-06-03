<?php

namespace App\Middleware;

use Core\Auth;

class AuthMiddleware
{
    public function handle(): void
    {
        if (Auth::guest()) {
            header('Location: /login');
            exit;
        }
    }
}