# Module Development Guide

## Module Structure

```markdown
my-module/
├── module.yaml
├── src/
│   ├── Controller/
│   │   └── MyController.php
│   ├── Block/
│   │   └── MyBlock.php
│   └── EventSubscriber.php
├── Templates/
│   └── my-template.html.twig
├── assets/
│   ├── css/
│   │   └── module.css
│   └── js/
│       └── module.js
├── Migrations/
│   └── Version20240101000000.php
├── routes.php
└── composer.json
```

## Module Manifest (module.yaml)

```yaml
name: My Module
id: vendor/my-module
description: A custom module for OOPress
type: module
version: 1.0.0
stability: stable

oopress:
  api: "^1.0"
  verified: "2024-01-01"

php:
  minimum: "8.2"

author:
  name: Your Name
  email: you@example.com
  url: https://example.com

license: MIT

dependencies:
  requires:
    "oopress/users": "^1.0"
  suggests:
    "oopress/media": "^1.0"

hooks:
  - content.save
  - user.login
  - cache.clear

autoload:
  psr4:
    "Vendor\\Module\\": "src/"
```

## Registering Routes (routes.php)

```php
<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Vendor\Module\Controller\MyController;

$collection = new RouteCollection();

$collection->add('my_module.hello', new Route(
    '/my-module/hello',
    ['_controller' => [MyController::class, 'hello']],
    methods: ['GET']
));

$collection->add('my_module.api', new Route(
    '/api/v1/my-module/data',
    ['_controller' => [MyController::class, 'getData']],
    methods: ['GET']
));

return $collection;
```

## Creating a Controller

`src/Controller/MyController.php`:

```php
<?php

namespace Vendor\Module\Controller;

use OOPress\Api\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MyController extends ApiController
{
    public function hello(Request $request): JsonResponse
    {
        return $this->success(['message' => 'Hello, World!']);
    }
    
    public function getData(Request $request): JsonResponse
    {
        $data = [
            'items' => ['item1', 'item2', 'item3'],
            'total' => 3,
        ];
        
        return $this->success($data);
    }
}
```

## Creating a Block

`src/Block/MyBlock.php`:

```php
<?php

namespace Vendor\Module\Block;

use OOPress\Block\BlockInterface;
use Symfony\Component\HttpFoundation\Request;

class MyBlock implements BlockInterface
{
    public function getId(): string
    {
        return 'my_block';
    }
    
    public function getLabel(): string
    {
        return 'My Custom Block';
    }
    
    public function getDescription(): string
    {
        return 'Displays custom content';
    }
    
    public function getModule(): string
    {
        return 'vendor/my-module';
    }
    
    public function getCategory(): string
    {
        return 'Custom';
    }
    
    public function render(Request $request, array $settings = []): string
    {
        return '<div class="my-block">Hello from my block!</div>';
    }
    
    public function getConfigForm(array $settings = []): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Block Title',
                'default' => $settings['title'] ?? 'My Block',
            ],
        ];
    }
    
    public function validateConfig(array $settings): array
    {
        $errors = [];
        
        if (empty($settings['title'])) {
            $errors['title'] = 'Title is required';
        }
        
        return $errors;
    }
    
    public function isCacheable(): bool
    {
        return true;
    }
    
    public function getCacheTags(): array
    {
        return ['my_block'];
    }
    
    public function getCacheContexts(): array
    {
        return ['user.roles'];
    }
}
```

## Hooking into Events

`src/EventSubscriber.php`:

```php
<?php

namespace Vendor\Module;

use OOPress\Event\HookSubscriberInterface;
use OOPress\Event\Event;
use OOPress\Log\Logger;

class EventSubscriber implements HookSubscriberInterface
{
    public function __construct(
        private readonly Logger $logger,
    ) {}
    
    public static function getSubscribedEvents(): array
    {
        return [
            'content.save' => 'onContentSave',
            'user.login' => ['onUserLogin', 10],
            'cache.clear' => 'onCacheClear',
        ];
    }
    
    public function onContentSave(Event $event): void
    {
        $content = $event->getContextValue('content');
        
        $this->logger->info('Content saved', [
            'content_id' => $content['id'] ?? null,
            'title' => $content['title'] ?? null,
        ]);
    }
    
    public function onUserLogin(Event $event): void
    {
        $userId = $event->getContextValue('user_id');
        
        $this->logger->info('User logged in', ['user_id' => $userId]);
    }
    
    public function onCacheClear(Event $event): void
    {
        // Invalidate custom cache
    }
}
```

## Database Migrations

`Migrations/Version20240101000000.php`:

```php
<?php

namespace Vendor\Module\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OOPress\Migration\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create my_module_table';
    }
    
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('my_module_data');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('value', 'text');
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(['id']);
    }
    
    public function down(Schema $schema): void
    {
        $schema->dropTable('my_module_data');
    }
}
```

## Templates

`Templates/my-template.html.twig`:

```html
{% extends 'base.html.twig' %}

{% block content %}
    <h1>{{ title }}</h1>
    
    <div class="my-module-content">
        {{ content|raw }}
    </div>
{% endblock %}
```

## Assets (assets.yaml)

```yaml
css:
  module:
    path: "assets/css/module.css"
    weight: 0
    media: "all"

js:
  module:
    path: "assets/js/module.js"
    weight: 0
    defer: true
```

## Best Practices
```markdown
1. Use @api annotations for public methods
2. Use @internal annotations for methods that may change
3. Follow PSR standards (PSR-4, PSR-12)
4. Write tests for your module
5. Document your code with PHPDoc
6. Version your module using semantic versioning
7. Declare dependencies in module.yaml
8. Use the event system instead of direct function calls
9. Cache expensive operations using the CacheManager
10. Log important actions using the Logger
```