# Module API Reference

## Module Manifest Fields

Required fields:
- name: Human-readable module name
- id: Unique identifier (vendor/name format)
- version: Semantic version (1.0.0)
- oopress.api: API compatibility constraint

Optional fields:
- description: Module description
- stability: stable|beta|experimental
- php.minimum: Minimum PHP version
- author: Author information
- license: License type
- dependencies: Required/suggested modules
- hooks: Event hooks used
- autoload: PSR-4 autoloading configuration

## Module Hooks

Declare hooks in module.yaml:

hooks:
  - content.save
  - user.login
  - cache.clear

## Service Registration

Modules can register services via `config/services.php`:
```php
return [
    'services' => [
        'my_module.service' => [
            'class' => Vendor\Module\MyService::class,
            'arguments' => ['@logger', '@database'],
        ],
    ],
];
```

## Console Commands

Register commands via event listener:
```php
$event->getApplication()->add(new MyCommand());

## API Endpoints

Add custom API endpoints via routes.php:

$collection->add('my_api.endpoint', new Route(
    '/api/v1/my-module/data',
    ['_controller' => [MyController::class, 'getData']],
    methods: ['GET']
));
```

## GraphQL Types

Add custom GraphQL types via event:

`$event->getTypes()->register('MyType', new MyType());`

## Dashboard Widgets

Add dashboard widgets via event:

`$event->addWidget(new MyWidget());`

## Permissions

Add custom permissions via `module.yaml`:

permissions:
  - 'view my data'
  - 'edit my data'

## Configuration

Modules can have their own config file:

`config/my_module.php`

Access via container:

`$config = $container->getParameter('my_module');`

## Translations

Module translations go in:

Resources/translations/
- messages.en.yaml
- messages.fr.yaml

## Assets

Module assets go in:

assets/css/
assets/js/
assets/images/

Declare in `assets.yaml` for compilation.

## Database Tables

Create tables via migrations in:

`Migrations/VersionYYYYMMDDHHMMSS.php`

## Cron Jobs

Schedule cron jobs via `module.yaml`:

cron:
  - schedule: "*/15 * * * *"
    command: "my_module:cleanup"

## Best Practices for Module API

1. Always use dependency injection
2. Follow semantic versioning
3. Declare all dependencies
4. Use events for extensibility
5. Cache expensive operations
6. Write unit tests
7. Document public methods with @api
8. Mark internal methods with @internal
9. Use type hints everywhere
10. Follow PSR standards