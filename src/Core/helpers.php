<?php

use OOPress\Models\Setting;

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default', array $params = []): string
    {
        global $translator;
        if (!$translator instanceof \OOPress\Core\I18n\Translator) {
            return $text;
        }
        return $translator->translate($text, $domain, $params);
    }
}

if (!function_exists('_e')) {
    function _e(string $text, string $domain = 'default', array $params = []): void
    {
        echo __($text, $domain, $params);
    }
}

if (!function_exists('_n')) {
    function _n(string $singular, string $plural, int $number, string $domain = 'default', array $params = []): string
    {
        global $translator;
        if (!$translator instanceof \OOPress\Core\I18n\Translator) {
            return $number == 1 ? $singular : $plural;
        }
        return $translator->translatePlural($singular, $plural, $number, $domain, $params);
    }
}

if (!function_exists('set_locale')) {
    function set_locale(string $locale): void
    {
        global $translator;
        if ($translator instanceof \OOPress\Core\I18n\Translator) {
            $translator->setLocale($locale);
        }
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string
    {
        global $translator;
        if ($translator instanceof \OOPress\Core\I18n\Translator) {
            return $translator->getLocale();
        }
        return 'en';
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return \OOPress\Models\Setting::get($key, $default);
    }
}

if (!function_exists('theme_asset')) {
    function theme_asset(string $path = ''): string
    {
        static $themeManager = null;
        if ($themeManager === null) {
            $themeManager = new \OOPress\Core\Theme\ThemeManager();
        }
        return $themeManager->getThemeAssetUrl($path);
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