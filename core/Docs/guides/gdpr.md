# GDPR Compliance Guide

## Built-in GDPR Features

### Self-Hosted Assets

All assets (CSS, JS, fonts) are self-hosted by default.

config/assets.php:
`'self_host_assets' => true,`

### Local Search

The search system uses your own database, not external services.

config/search.php:
`'backend' => 'database',`

### Local Cache

All caching is done locally on your server.

config/cache.php:
`'default_backend' => 'file',`

### Local Logging

Logs are stored on your server, not sent to external services.

config/logging.php:
`'handlers' => ['file', 'database'],`

## Cookie Consent

Enable consent banner in template:

`{{ gdpr_consent() }}`

Configure purposes:
```php
$consent = new GdprConsent('site_consent');
$consent->addPurpose('analytics', 'Analytics', 'Help us improve our website');
$consent->setPrivacyPolicyUrl('/privacy');
echo $consent->render();
```

## Data Collected

### User Accounts
- Username
- Email address
- Hashed password
- Roles
- Created/updated timestamps

### Content
- Title, body, summary
- Author ID
- Created/updated timestamps
- IP address (in logs)

### Logs
- Request URI
- Request method
- IP address
- User agent
- Timestamp

## Data Retention

Configure log retention in `config/logging.php`:
```php
'retention' => [
    'database_days' => 90,
    'file_days' => 30,
]
```

## User Rights

### Right to Access

Users can view their data at `/admin/users/me`

### Right to Rectification

Users can edit their profile information

### Right to Erasure

Admins can delete user accounts:

`php oopress user:delete user@example.com`

### Right to Data Portability

Export user data:

`php oopress user:export user@example.com`

## Data Anonymization

Anonymize user data:

`php oopress user:anonymize 123`

This will:
- Replace username with anonymous_123
- Replace email with anonymous_123@example.com
- Clear all personal data

## Consent Management

### Check Consent in JavaScript
```js
if (getConsent()?.analytics) {
    // Initialize analytics
}
```

### Update Consent
```js
saveConsent({
    necessary: true,
    preferences: true,
    analytics: false,
    marketing: false
});
```

## Audit Trail

All security-related actions are logged:
```php
$logger->info('User deleted', [
    'deleted_user_id' => $userId,
    'deleted_by' => $adminId,
    'ip' => $request->getClientIp(),
]);
```

## GDPR Checklist

- [ ] Self-host all assets (CSS, JS, fonts)
- [ ] Use local search (database backend)
- [ ] Enable cookie consent banner
- [ ] Configure data retention periods
- [ ] Document data collection in privacy policy
- [ ] Implement user data export
- [ ] Implement user data deletion
- [ ] Train staff on GDPR compliance
- [ ] Keep software updated
- [ ] Monitor security logs