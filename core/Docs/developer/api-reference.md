# API Reference

## Core Classes

### PathResolver
Location: `core/Path/PathResolver.php`
Status: @api

Methods:
- getProjectRoot(): string
- getPublicRoot(): string
- getCorePath(): string
- getModulesPath(): string
- getThemesPath(): string
- getConfigPath(): string
- getVarPath(): string
- getFilesPath(): string
- getVendorPath(): string
- getModulePath(string $moduleId): string
- getThemePath(string $themeId): string
- getSettingsFile(): string

### ExtensionLoader
Location: `core/Extension/ExtensionLoader.php`
Status: @api

Methods:
- discoverModules(): void
- discoverThemes(): void
- getModule(string $id): ?ExtensionManifest
- getTheme(string $id): ?ExtensionManifest
- getModules(): array
- getThemes(): array
- hasExtension(string $id, ExtensionType $type): bool

### HookDispatcher
Location: `core/Event/HookDispatcher.php`
Status: @api

Methods:
- dispatch(Event $event, ?string $eventName = null): Event
- filter(FilterEvent $event, ?string $eventName = null): mixed
- applyFilters(string $hookName, mixed $value, array $context = []): mixed
- doAction(string $hookName, array $context = []): void
- addListener(string $eventName, callable $listener, int $priority = 0): void

### CacheManager
Location: `core/Cache/CacheManager.php`
Status: @api

Methods:
- get(string $key, mixed $default = null): mixed
- set(string $key, mixed $value, ?int $ttl = null): bool
- delete(string $key): bool
- clear(): bool
- has(string $key): bool
- tags(array $tags): self
- invalidateTag(string $tag): bool
- invalidateTags(array $tags): bool

### ContentRepository
Location: `core/Content/ContentRepository.php`
Status: @api

Methods:
- find(int $id, ?string $language = null): ?Content
- findBySlug(string $slug, string $language): ?Content
- findByType(string $contentType, ?string $language = null, int $limit = 50, int $offset = 0): array
- save(Content $content): void
- delete(Content $content): void

### BlockManager
Location: `core/Block/BlockManager.php`
Status: @api

Methods:
- getBlockDefinition(string $id): ?BlockDefinition
- getAllBlockDefinitions(): array
- getBlocksForRegion(string $region): array
- assignBlock(string $blockId, string $region, int $weight = 0, array $settings = []): void
- unassignBlock(string $blockId, string $region): void
- renderRegion(string $region, Request $request): string

### Logger
Location: `core/Log/Logger.php`
Status: @api

Methods:
- log($level, $message, array $context = []): void
- debug($message, array $context = []): void
- info($message, array $context = []): void
- notice($message, array $context = []): void
- warning($message, array $context = []): void
- error($message, array $context = []): void
- critical($message, array $context = []): void
- alert($message, array $context = []): void
- emergency($message, array $context = []): void
- channel(string $channel): self

### PasswordHasher
Location: `core/Security/PasswordHasher.php`
Status: @api

Methods:
- hash(string $plainPassword): string
- verify(string $plainPassword, string $hashedPassword): bool
- needsRehash(string $hashedPassword): bool
- validateStrength(string $password, array $requirements = []): array
- generateRandom(int $length = 16, bool $includeSymbols = true): string

## Events

### Core Events
- kernel.boot - Dispatched when kernel boots
- kernel.request - Dispatched before request handling
- kernel.response - Dispatched after response created
- kernel.exception - Dispatched on uncaught exception
- kernel.shutdown - Dispatched during shutdown

### Content Events
- content.save - Dispatched when content is saved
- content.delete - Dispatched when content is deleted
- content.publish - Dispatched when content is published

### User Events
- user.login - Dispatched on successful login
- user.logout - Dispatched on logout
- user.register - Dispatched on user registration

### Cache Events
- cache.clear - Dispatched when cache is cleared
- cache.invalidate - Dispatched when cache tags are invalidated

### Search Events
- search.rebuild - Dispatched when search index is rebuilt

## Hooks (Filters)

### Content Hooks
- content.title - Filter content title before display
- content.body - Filter content body before display
- content.url - Filter content URL

### Block Hooks
- block.render - Filter block output before display
- block.settings - Filter block settings before save

### API Hooks
- api.response - Filter API response before sending
- api.error - Filter API error response