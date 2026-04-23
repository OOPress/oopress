# Theme Development Guide

## Theme Structure

```
themes/your-theme/
├── theme.json # Theme metadata
├── functions.php # Theme functions (optional)
├── assets/ # CSS, JS, images
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
└── views/ # Template files
├── layouts/
│   └── app.php # Main layout
├── home.php # Homepage template
├── page.php # Page template
├── post/
│   └── single.php # Single post template
├── archive/
│   ├── category.php # Category archive
│   └── tag.php # Tag archive
└── errors/
    └── 404.php # 404 template
```

## theme.json Example

```json
{
    "name": "My Theme",
    "description": "A custom OOPress theme",
    "version": "1.0.0",
    "author": "Your Name",
    "screenshot": "screenshot.png"
}
```

## Template Variables

### Homepage (home.php)

| Variable	| Description |
| :-------- | ----------: |
|$posts	| Array of post objects |
|$title	Site | title |
|$tagline	| Site tagline |
|$show_excerpt	| Show excerpts setting |
|$current_page	| Current page number |
|$total_pages	| Total pages for pagination |


### Single Post (post/single.php)

| Variable	| Description |
| :-------- | ----------: |
|$post	| Post object |
|$categories | title |
|$tags	| Site tagline |
|$auth	| Show excerpts setting |
|$seo	| Current page number |

### Page (page.php)

| Variable	| Description |
| :-------- | ----------: |
|$page	| Page object |
|$children | Child pages (if any) |
|$content	| Page content |


## Helper Functions

### __($text, $domain = 'default')

Translate text:

```php
<h1><?= __('Welcome to OOPress') ?></h1>
```

### setting($key, $default = null)

Get site setting:

```php
<?= setting('site_title') ?>
```

### auth()

Get auth object or null:

```php
<?php if (auth() && auth()->check()): ?>
    <p>Welcome back, <?= auth()->user()->display_name ?></p>
<?php endif; ?>
```

### oop_menu()

Get pages for navigation menu:

```php
<nav>
    <?php foreach (oop_menu() as $page): ?>
        <a href="<?= $page->getUrl() ?>"><?= $page->title ?></a>
    <?php endforeach; ?>
</nav>
```

### theme_asset($path)

Get theme asset URL:

```php
<link rel="stylesheet" href="<?= theme_asset('css/style.css') ?>">
```

## Custom Page Templates

Create page-custom.php in your theme views directory:

```php
<?php $this->layout('layouts/app') ?>

<div class="custom-page">
    <?= $page->content ?>
</div>
```

Then select "custom" as the page template in the admin.

## Theme Activation
1. Upload your theme to themes/ directory
2. Go to /admin/themes
3. Click "Activate" on your theme

## Best Practices
1. Use the layout system - Extend layouts/app.php for consistent design
2. Escape output - Always use $this->e() to prevent XSS
3. Follow the template hierarchy - Use the correct template names
4. Keep logic in controllers - Views should only display data
5. Use helper functions - Leverage built-in helpers for common tasks
