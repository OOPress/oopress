# Installation Guide

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+ / PostgreSQL / SQLite
- Composer (for development)

## Quick Install

### Step 1: Download

Download the latest release from `oopress.org/download`

### Step 2: Upload

Upload the files to your web server's document root.

### Step 3: Run Installer

Navigate to `https://yourdomain.com/install` and follow the installation wizard.

### Step 4: Complete

After installation, you'll be redirected to the admin panel.

## Manual Installation

### Using Composer

```markdown
composer create-project oopress/oopress my-site
cd my-site
php oopress install

```

### Using Git

```markdown
git clone https://github.com/OOPress/oopress.git my-site
cd my-site
composer install
php oopress install

```

## Configuration

The main configuration file is config/core.php. Database credentials are stored in settings.php (excluded from version control).

## Next Steps

- Create your first content type
- Install modules
- Customize your theme