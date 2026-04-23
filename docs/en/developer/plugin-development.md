# Plugin Development Guide

## Plugin 

```
plugins/your-plugin/
├── plugin.json # Plugin metadata
└── plugin.php # Main plugin file
```

## plugin.json Example

```json
{
    "name": "My Plugin",
    "description": "Extends OOPress functionality",
    "version": "1.0.0",
    "author": "Your Name"
}
```

## plugin.php Example

```php
<?php

/*
Plugin Name: My Plugin
Description: Extends OOPress functionality
Version: 1.0.0
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Hook into initialization
add_action('init', function() {
    // Your initialization code
});

// Add a filter
add_filter('site_title', function($title) {
    return $title . ' - Powered by My Plugin';
});

// Add admin menu item
add_action('admin_menu', function() {
    add_menu_page('My Plugin', 'My Plugin', 'manage_options', 'my-plugin', 'my_plugin_page');
});

function my_plugin_page() {
    echo '<h1>My Plugin Settings</h1>';
}
```

## Available Hooks

### Action Hooks

| Hook	    | Description | Parameters |
| :-------- | :---------: | --------: |
| init	    | After system initialization | None |
| admin_menu | Building admin menu | None |
| before_homepage_render | Before homepage renders | None |
| after_post_save | After post is saved | $post |
| plugin_activated_{$slug} | Plugin activated | None |
| plugin_deactivated_{$slug} | Plugin deactivated | None |


### Filter Hooks

| Filter    | Description | Parameters |
| :-------- | :---------: | --------: |
| site_title  | Modify site title | $title |
| post_content | Modify post content | $content, $post |
| comment_content | Modify comment content | $content |
| excerpt_length | Modify excerpt length | $length |


### Adding Custom Hooks

In your code:

```php
// Action
do_action('my_custom_action', $data);

// Filter
$value = apply_filters('my_custom_filter', $value, $arg1, $arg2);
```

### Plugin API Reference

```php
add_action($hook, $callback, $priority = 10, $accepted_args = 1)
```
Add an action hook.

```php
do_action($hook, ...$args)
```
Execute action hooks.

```php
add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
```
Add a filter hook.

```php
apply_filters($hook, $value, ...$args)
```
Apply filter hooks.

### Activation/Deactivation Hooks

```php
// On activation
add_action('plugin_activated_my-plugin', function() {
    // Create database tables, set options, etc.
});

// On deactivation  
add_action('plugin_deactivated_my-plugin', function() {
    // Clean up, remove options, etc.
});
```

### Plugin Best Practices
1. Unique slug - Use a unique slug for your plugin
2. Prefix functions - Prefix functions to avoid conflicts
3. Check permissions - Verify user capabilities before actions
4. Sanitize input - Always sanitize user input
5. Escape output - Escape output in admin pages
6. Use translations - Make your plugin translatable

### Testing Your Plugin
1. Create your plugin directory in plugins/
2. Add plugin.json and plugin.php
3. Go to /admin/plugins
4. Activate your plugin
5. Test functionality

### Debugging Plugins
Enable debug mode in .env:

```text
APP_ENV=local
APP_DEBUG=true
```

Check PHP error logs:

```bash
tail -f /var/log/php8.2-fpm.log
```
