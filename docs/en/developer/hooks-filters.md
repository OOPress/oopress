# Hooks and Filters Reference

## Action Hooks

### System Hooks

| Hook | Description | Called |
|------|-------------|--------|
| `init` | System initialization | After bootstrap |
| `shutdown` | System shutdown | Before script ends |

### Post Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `before_post_save` | Before post saved | `$post` |
| `after_post_save` | After post saved | `$post` |
| `before_post_delete` | Before post deleted | `$post_id` |
| `after_post_delete` | After post deleted | `$post_id` |

### User Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `user_register` | New user registered | `$user_id` |
| `user_login` | User logged in | `$user` |
| `user_logout` | User logged out | `$user_id` |

### Admin Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `admin_menu` | Building admin menu | None |
| `admin_init` | Admin initialization | None |
| `admin_enqueue_scripts` | Enqueue admin assets | None |

### Plugin Hooks

| Hook | Description | Parameters |
|------|-------------|------------|
| `plugin_activated_{$slug}` | Plugin activated | None |
| `plugin_deactivated_{$slug}` | Plugin deactivated | None |
| `plugin_loaded_{$slug}` | Plugin loaded | None |

## Filter Hooks

### Content Filters

| Filter | Description | Parameters |
|--------|-------------|------------|
| `post_content` | Modify post content | `$content`, `$post` |
| `post_title` | Modify post title | `$title`, `$post` |
| `post_excerpt` | Modify post excerpt | `$excerpt`, `$post` |
| `comment_content` | Modify comment content | `$content` |

### Site Settings

| Filter | Description | Parameters |
|--------|-------------|------------|
| `site_title` | Modify site title | `$title` |
| `site_tagline` | Modify site tagline | `$tagline` |
| `excerpt_length` | Modify excerpt length | `$length` |
| `posts_per_page` | Modify posts per page | `$count` |

### Theme Filters

| Filter | Description | Parameters |
|--------|-------------|------------|
| `theme_asset_url` | Modify asset URL | `$url`, `$path` |
| `theme_view_path` | Modify view path | `$path` |

### Security Filters

| Filter | Description | Parameters |
|--------|-------------|------------|
| `allowed_html_tags` | Modify allowed HTML tags | `$tags` |
| `allowed_php_functions` | Modify allowed PHP functions | `$functions` |

## Usage Examples

### Custom Excerpt Length

```php
add_filter('excerpt_length', function($length) {
    return 100; // 100 characters
});
```

### Modify Post Content

```php
add_filter('post_content', function($content, $post) {
    return $content . '<p>Written by ' . $post->author()->display_name . '</p>';
}, 10, 2);
```

### Modify Post Content

```php
add_filter('post_content', function($content, $post) {
    return $content . '<p>Written by ' . $post->author()->display_name . '</p>';
}, 10, 2);
```

### Add Custom Admin Menu

```php
add_action('admin_menu', function() {
    add_menu_page(
        'Custom Page',     // Page title
        'Custom Menu',     // Menu title
        'manage_options',  // Capability
        'custom-page',     // Menu slug
        function() {
            echo '<h1>Custom Page</h1>';
        }
    );
});
```

### Redirect After Login

```php
add_filter('login_redirect', function($url, $user) {
    return '/dashboard';
}, 10, 2);
```

### Creating Custom Hooks

In your code:

```php
// Define a hook point
do_action('my_custom_action', $data);

// Define a filter point  
$value = apply_filters('my_custom_filter', $value, $arg);
```

### Priority System

Hooks run in priority order (lower numbers run first):

```php
// Runs first (priority 5)
add_action('init', function() {
    // Do something early
}, 5);

// Runs last (priority 100)
add_action('init', function() {
    // Do something late
}, 100);
```

### Removing Hooks

```php
remove_action('init', 'some_function', 10);
remove_filter('post_content', 'some_filter', 10);
```

## Create Directory Structure

```bash
mkdir -p docs/{en,de,fr,it,es}/{user,developer}
```

