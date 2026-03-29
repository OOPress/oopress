# Security Guide

## Authentication

OOPress uses session-based authentication with secure cookies.

### Password Policy

Default password requirements:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number

Configure in config/security.php:

```php
'password_policy' => [
    'min_length' => 12,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_number' => true,
    'require_symbol' => true,
]
```

### Session Security

Configure in config/security.php:

```php
'session' => [
    'lifetime' => 7200,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'lax',
]
```

## Authorization (RBAC)

### Default Roles

```text
ROLE_ANONYMOUS - Not logged in
ROLE_USER - Authenticated user
ROLE_EDITOR - Can edit content
ROLE_ADMIN - Full access
```

### Checking Permissions

In controller:
```php
if ($this->authorization->isGranted($user, 'edit', $content)) {
    // User can edit this content
}
```

In template:
```html
{% if is_granted('edit', content) %}
    <a href="/content/{{ content.id }}/edit">Edit</a>
{% endif %}
```

### Custom Voter

Create `src/Security/Voter/CustomVoter.php`:

```php
<?php

namespace Vendor\Module\Security\Voter;

use OOPress\Security\Voter\VoterInterface;
use OOPress\Security\UserInterface;

class CustomVoter implements VoterInterface
{
    public function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'custom_action';
    }
    
    public function vote(UserInterface $user, string $attribute, mixed $subject): int
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return self::ACCESS_GRANTED;
        }
        return self::ACCESS_DENIED;
    }
}
```

## CSRF Protection

In forms:
```php
<form method="POST">
    {{ csrf_field() }}
    <!-- form fields -->
</form>
```

In API:
```php
$csrfToken = $request->request->get('_csrf_token');
if (!$csrfTokenManager->validateToken('form_name', $csrfToken)) {
    throw new \Exception('Invalid CSRF token');
}
```

## XSS Prevention

Twig auto-escapes all output by default:
```html
{{ user_input }}  <!-- Automatically escaped -->
{{ user_input|raw }}  <!-- Use with caution! -->
```

### Content Security Policy

Configure in `config/security.php`:
```php
'csp' => [
    'enabled' => true,
    'directives' => [
        'default-src' => ["'self'"],
        'script-src' => ["'self'"],
        'style-src' => ["'self'"],
        'img-src' => ["'self'", 'data:'],
        'font-src' => ["'self'"],
        'connect-src' => ["'self'"],
    ],
]
```

## SQL Injection Prevention

Always use parameterized queries:
```php
$result = $connection->fetchAll(
    'SELECT * FROM users WHERE id = :id',
    ['id' => $userId]
);
```

## File Upload Security

Configure in `config/media.php`:
```php
'allowed_extensions' => ['jpg', 'png', 'gif', 'pdf'],
'max_file_size' => 5 * 1024 * 1024,
```

## Security Headers

OOPress automatically adds:
```php
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

## Logging Security Events
```php
$securityLogger = $logger->channel('security');
$securityLogger->warning('Failed login attempt', [
    'ip' => $request->getClientIp(),
    'username' => $username,
]);
```

## Two-Factor Authentication (2FA)

Enable 2FA for admin accounts:

`php oopress user:2fa enable admin`

## Best Practices

1. Use strong passwords - Enforce password policy
2. Enable HTTPS - Always use TLS
3. Regular updates - Keep OOPress and modules updated
4. Backup regularly - Database and files
5. Monitor logs - Check security logs daily
6. Limit login attempts - Prevents brute force
7. Use firewall - Restrict access to admin area
8. Audit user accounts - Remove inactive users