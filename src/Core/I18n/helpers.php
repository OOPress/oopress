<?php

declare(strict_types=1);

use OOPress\Core\I18n\Translator;

if (!function_exists('__')) {
    /**
     * Translate string
     */
    function __(string $text, string $domain = 'default', array $params = []): string
    {
        global $translator;
        if (!$translator instanceof Translator) {
            return $text;
        }
        return $translator->translate($text, $domain, $params);
    }
}

if (!function_exists('_e')) {
    /**
     * Echo translated string
     */
    function _e(string $text, string $domain = 'default', array $params = []): void
    {
        echo __($text, $domain, $params);
    }
}

if (!function_exists('_n')) {
    /**
     * Translate plural
     */
    function _n(string $singular, string $plural, int $number, string $domain = 'default', array $params = []): string
    {
        global $translator;
        if (!$translator instanceof Translator) {
            return $number == 1 ? $singular : $plural;
        }
        return $translator->translatePlural($singular, $plural, $number, $domain, $params);
    }
}

if (!function_exists('set_locale')) {
    /**
     * Set current locale
     */
    function set_locale(string $locale): void
    {
        global $translator;
        if ($translator instanceof Translator) {
            $translator->setLocale($locale);
        }
    }
}

if (!function_exists('get_locale')) {
    /**
     * Get current locale
     */
    function get_locale(): string
    {
        global $translator;
        if ($translator instanceof Translator) {
            return $translator->getLocale();
        }
        return 'en';
    }
}