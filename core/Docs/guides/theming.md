# Theming Guide

## Theme Structure

```markdown
my-theme/
├── theme.yaml
├── Templates/
│   ├── base.html.twig
│   ├── page.html.twig
│   ├── article.html.twig
│   └── partials/
│       ├── header.html.twig
│       ├── footer.html.twig
│       └── sidebar.html.twig
├── assets/
│   ├── css/
│   │   └── theme.css
│   ├── js/
│   │   └── theme.js
│   └── images/
│       └── screenshot.png
└── screenshot.png
```

## Theme Manifest (theme.yaml)

```yaml
name: My Theme
description: A custom theme for OOPress
version: 1.0.0
author: Your Name

regions:
  header:
    label: Header
    description: Site header region
  content:
    label: Content
    description: Main content area
  sidebar:
    label: Sidebar
    description: Sidebar region
  footer:
    label: Footer
    description: Site footer region

settings:
  logo:
    type: image
    label: Site Logo
    default: "/assets/images/logo.png"
  primary_color:
    type: color
    label: Primary Color
    default: "#007bff"
```

## Base Template (Templates/base.html.twig)

```html
<!DOCTYPE html>
<html lang="{{ app.language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{{ site_name }}{% endblock %}</title>
    {{ assets() }}
    {% block styles %}{% endblock %}
</head>
<body>
    <div class="site-wrapper">
        <header class="site-header">
            {% block header %}
                {{ render_region('header') }}
            {% endblock %}
        </header>
        
        <main class="site-main">
            {% block content %}
                {{ render_region('content') }}
            {% endblock %}
        </main>
        
        <aside class="site-sidebar">
            {% block sidebar %}
                {{ render_region('sidebar') }}
            {% endblock %}
        </aside>
        
        <footer class="site-footer">
            {% block footer %}
                {{ render_region('footer') }}
            {% endblock %}
        </footer>
    </div>
    {% block scripts %}{% endblock %}
</body>
</html>
```

## Page Template (Templates/page.html.twig)

```html
{% extends 'base.html.twig' %}

{% block title %}{{ content.title }} | {{ parent() }}{% endblock %}

{% block content %}
    <article class="page">
        <header class="page-header">
            <h1>{{ content.title }}</h1>
        </header>
        <div class="page-content">
            {{ content.body|raw }}
        </div>
    </article>
{% endblock %}
```

## Article Template (Templates/article.html.twig)

```html
{% extends 'base.html.twig' %}

{% block title %}{{ content.title }} | {{ parent() }}{% endblock %}

{% block content %}
    <article class="article">
        <header class="article-header">
            <h1>{{ content.title }}</h1>
            <div class="article-meta">
                <span class="date">{{ content.created_at|date('F j, Y') }}</span>
                <span class="author">By {{ content.author.username }}</span>
            </div>
        </header>
        
        {% if content.summary %}
            <div class="article-summary">
                {{ content.summary }}
            </div>
        {% endif %}
        
        <div class="article-content">
            {{ content.body|raw }}
        </div>
        
        {% if content.fields.tags %}
            <div class="article-tags">
                <strong>Tags:</strong>
                {% for tag in content.fields.tags %}
                    <a href="/tag/{{ tag|slug }}">{{ tag }}</a>
                {% endfor %}
            </div>
        {% endif %}
    </article>
{% endblock %}
```

## Theme Functions


render_region
Renders all blocks assigned to a region:
`{{ render_region('header') }}`

render_block
Renders a specific block by ID:
`{{ render_block('oopress/system/user_menu') }}`

asset_url
Generates URL for an asset:
`<img src="{{ asset_url('images/logo.png') }}" alt="Logo">`

url
Generates URL for a named route:
`<a href="{{ url('content.view', {id: content.id}) }}">Read more</a>`

t
Translates a string:
`{{ t('Read more') }}`

## Theme CSS (assets/css/theme.css)

```css
/* Site Layout */
.site-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.site-header {
    background: {{ theme_settings.primary_color }};
    padding: 20px;
}

.site-main {
    display: flex;
    gap: 40px;
    margin: 40px 0;
}

.site-sidebar {
    flex: 1;
}


/* Responsive */
@media (max-width: 768px) {
    .site-main {
        flex-direction: column;
    }
}
```

## Activating a Theme

```markdown
1. Go to Appearance > Themes in the admin panel
2. Find your theme in the list
3. Click Activate
```

## Creating a Child Theme

```markdown
child-theme/
├── theme.yaml
├── Templates/
│   └── article.html.twig
└── assets/
    └── css/
        └── child.css
```

Child theme theme.yaml:

```yaml
name: Child Theme
description: Child theme of My Theme
version: 1.0.0
parent: my-theme
```

## Best Practices

```markdown
1. Use semantic HTML5 elements
2. Make your theme responsive with CSS media queries
3. Use Twig's inheritance for consistent layouts
4. Keep templates simple - move logic to blocks
5. Use the asset pipeline for CSS/JS
6. Test in multiple browsers
7. Follow WCAG accessibility guidelines
```