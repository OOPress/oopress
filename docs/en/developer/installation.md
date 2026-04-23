# Developer Installation Guide

## System Requirements

- PHP 8.2 or higher
- MySQL 5.7 / MariaDB 10.3 or higher
- Apache 2.4+ or Nginx 1.18+
- Composer
- Git (optional)

## Required PHP Extensions

- PDO MySQL
- JSON
- MBString
- OpenSSL
- Fileinfo
- cURL
- Zip

## Quick Installation

### 1. Clone the Repository

```bash
git clone https://github.com/OOPress/oopress.git
cd oopress
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 4. Run Migrations

```bash
php cli/oopress migrate
```

### 5. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 cache/
```

### 6. Configure Web Server

Apache
Create a virtual host pointing to the public/ directory:

```bash
<VirtualHost *:80>
    ServerName oopress.local
    DocumentRoot /var/www/oopress/public
    
    <Directory /var/www/oopress/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Nginx

```bash
server {
    listen 80;
    server_name oopress.local;
    root /var/www/oopress/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Create Admin User

```bash
php cli/oopress create:user --username=admin --email=admin@example.com --password=yourpassword --role=admin
```

### 8. Access Your Site

  * Frontend: http://oopress.local

  * Admin: http://oopress.local/admin

## Docker Installation

```bash
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php cp .env.example .env
docker-compose exec php php cli/oopress migrate
```

## Troubleshooting

### Cache Directory Not Writable

```bash
sudo chown -R www-data:www-data storage/cache/
```

### Database Connection Failed

Check your .env database credentials:

```bash
DB_HOST=localhost
DB_NAME=oopress
DB_USER=root
DB_PASS=yourpassword
```

### White Screen After Installation

Check PHP error logs:

```bash
tail -f /var/log/php8.2-fpm.log
```

Enable debug mode in .env:

```text
APP_ENV=local
APP_DEBUG=true
```

## Next Steps

[Theme Development](theme-development.md)

[Plugin Development](plugin-development.md)

[Hooks and Filters](hooks-filters.md)

[API Reference](api-reference.md)
