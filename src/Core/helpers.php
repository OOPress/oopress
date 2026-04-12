<?php

if (!function_exists('setting')) {
    /**
     * Get a site setting
     */
    function setting(string $key, $default = null)
    {
        return OOPress\Models\Setting::get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            static $auth = null;
            if ($auth === null) {
                $auth = new \OOPress\Core\Auth(new \OOPress\Core\Session());
            }
            return $auth;
        }
        return null;
    }
}