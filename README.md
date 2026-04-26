<img src="themes/default/assets/images/logo-dark.svg" alt="Official OOPress Logo" width="220" height="220">


[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-Apache--2.0-green.svg)](LICENSE)
[![Composer](https://img.shields.io/badge/composer-2.0%2B-orange.svg)](https://getcomposer.org)

# OOPress

Lean, modern PHP CMS built on clean OOP architecture.

## Philosophy

- No framework lock-in (Symfony/Laravel free)
- Minimal dependencies, maximum control
- Modern PHP 8.2+ with strict typing

## Features

✅ Modern Architecture - MVC, OOP, PSR standards
✅ Easy to Use - Intuitive admin interface
✅ SEO Ready - Built-in SEO tools
✅ Multi-language - i18n support
✅ GDPR Compliant - Cookie consent banner
✅ Extensible - Plugin and theme system
✅ Fast - Built-in caching
✅ Secure - Input validation, CSRF protection


## Requirements

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Apache / Nginx


## Quick Start

### Web Installer (Recommended)

1. Extract to your web directory
2. Visit your domain
3. Follow the installation wizard

### Command Line

```bash
# Extract to web directory
cd /var/www/html
unzip oopress-alpha-1.zip

# Install dependencies
composer install --no-dev

# Copy environment config
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Run migrations
php cli/oopress migrate
```

### From GitHub

```bash
# Clone the repo
git clone https://github.com/OOPress/oopress.git
cd oopress

# Copy environment config
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Install dependencies
composer install

# Run migrations
php cli/oopress migrate

# Start development server
php cli/oopress serve
```

## Documentation

[User Guide](docs/en/user/getting-started.md)
[Developer Guide](docs/en/developer/)
[API Reference]()

## License

[Apache License 2.0](LICENSE) 

## Links

[Website](https://oopress.org)
[Documentation](https://docs.oopress.org)
[GitHub](https://github.com/OOPress/oopress)
