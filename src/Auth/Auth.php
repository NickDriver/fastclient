<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\User;

class Auth
{
    private static ?User $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function login(User $user): void
    {
        $_SESSION['user_id'] = $user->id;
        self::$user = $user;

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        self::$user = null;
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?User
    {
        if (self::$user !== null) {
            return self::$user;
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        self::$user = User::find($_SESSION['user_id']);

        if (self::$user === null) {
            unset($_SESSION['user_id']);
        }

        return self::$user;
    }

    public static function id(): ?string
    {
        return self::user()?->id;
    }
}
