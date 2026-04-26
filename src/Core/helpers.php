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

if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        \OOPress\Core\Plugin\Hook::addAction($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, ...$args): void
    {
        \OOPress\Core\Plugin\Hook::doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        \OOPress\Core\Plugin\Hook::addFilter($hook, $callback, $priority, $acceptedArgs);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, $value, ...$args): mixed
    {
        return \OOPress\Core\Plugin\Hook::applyFilters($hook, $value, ...$args);
    }
}

if (!function_exists('cache')) {
    function cache(): \OOPress\Core\Cache\CacheManager
    {
        static $cache = null;
        if ($cache === null) {
            $cache = new \OOPress\Core\Cache\CacheManager();
        }
        return $cache;
    }
}

if (!function_exists('cache_remember')) {
    function cache_remember(string $key, callable $callback, ?int $ttl = null)
    {
        return cache()->remember($key, $callback, $ttl);
    }
}

if (!function_exists('oop_menu')) {
    function oop_menu(): array
    {
        return \OOPress\Models\Page::getMenuPages();
    }
}

if (!function_exists('oopress_version')) {
    function oopress_version(): string
    {
        return '1.0.0';
    }
}

if (!function_exists('oopress_name')) {
    function oopress_name(): string
    {
        return 'OOPress';
    }
}