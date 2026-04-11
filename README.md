![OOPress Logo](public/assets/images/logo/oopress_logo_dark_xs.png)

[![PHP Version](https://img.shields.io/badge/php-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-Apache--2.0-green.svg)](LICENSE)
[![Composer](https://img.shields.io/badge/composer-2.0%2B-orange.svg)](https://getcomposer.org)

# OOPress

Lean, modern PHP CMS built on clean OOP architecture.

## Philosophy

- No framework lock-in (Symfony/Laravel free)
- Minimal dependencies, maximum control
- Modern PHP 8.2+ with strict typing

## Quick Start

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
```
