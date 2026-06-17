<?php
namespace App\Core;
use App\Models\User;
class Auth {
    public static function login(User $user): void {
        $_SESSION['user_id'] = $user->id;
    }

    public static function logout(): void {
        unset($_SESSION['user_id']);
        session_destroy();
    }

    public static function user(): ?User {
        if (isset($_SESSION['user_id'])) {
            return User::find($_SESSION['user_id']);
        }
        return null;
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }
}