<?php

/*
Plugin Name: Demo Plugin
Description: A demo plugin for OOPress
Version: 1.0.0
Author: OOPress
*/

// Example action hook
add_action('init', function() {
    error_log('Demo plugin loaded!');
});

// Example filter hook
add_filter('site_title', function($title) {
    return $title . ' - Powered by Demo Plugin';
});