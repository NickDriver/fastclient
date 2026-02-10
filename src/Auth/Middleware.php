<?php

declare(strict_types=1);

namespace App\Auth;

class Middleware
{
    public function handle(): bool|string
    {
        if (!Auth::check()) {
            // Store intended URL for redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
        }

        return true;
    }
}
