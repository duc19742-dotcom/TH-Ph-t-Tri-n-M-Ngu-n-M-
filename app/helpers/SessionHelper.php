<?php

class SessionHelper
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::start();
        return isset($_SESSION['username']);
    }

    public static function isAdmin()
    {
        self::start();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function getRole()
    {
        self::start();
        return $_SESSION['role'] ?? 'guest';
    }

    public static function getUsername()
    {
        self::start();
        return $_SESSION['username'] ?? '';
    }
}