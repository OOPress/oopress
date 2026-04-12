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