<?php

declare(strict_types=1);

namespace OOPress\Tests\Unit\Block;

use OOPress\Tests\TestCase;
use OOPress\Block\BlockManager;
use OOPress\Block\BlockDefinition;
use OOPress\Block\BlockInterface;
use OOPress\Extension\ExtensionLoader;
use OOPress\Path\PathResolver;
use OOPress\Event\HookDispatcher;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test BlockManager functionality.
 * 
 * @internal
 */
class BlockManagerTest extends TestCase
{
    private BlockManager $blockManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $connection = $this->createMock(Connection::class);
        $extensionLoader = $this->createMock(ExtensionLoader::class);
        $pathResolver = $this->createMock(PathResolver::class);
        $dispatcher = $this->createMock(HookDispatcher::class);
        
        $this->blockManager = new BlockManager(
            $connection,
            $extensionLoader,
            $pathResolver,
            $dispatcher
        );
    }
    
    public function testRegisterBlock(): void
    {
        $definition = new BlockDefinition(
            id: 'test_block',
            label: 'Test Block',
            module: 'test_module',
            class: TestBlock::class
        );
        
        $this->blockManager->registerBlock($definition);
        
        $retrieved = $this->blockManager->getBlockDefinition('test_block');
        
        $this->assertNotNull($retrieved);
        $this->assertEquals('Test Block', $retrieved->label);
    }
    
    public function testGetAllBlockDefinitions(): void
    {
        $definitions = $this->blockManager->getAllBlockDefinitions();
        
        $this->assertIsArray($definitions);
    }
    
    public function testGetBlocksByCategory(): void
    {
        $definition1 = new BlockDefinition(
            id: 'block1',
            label: 'Block 1',
            module: 'test',
            class: TestBlock::class,
            category: 'General'
        );
        
        $definition2 = new BlockDefinition(
            id: 'block2',
            label: 'Block 2',
            module: 'test',
            class: TestBlock::class,
            category: 'Navigation'
        );
        
        $this->blockManager->registerBlock($definition1);
        $this->blockManager->registerBlock($definition2);
        
        $byCategory = $this->blockManager->getBlocksByCategory();
        
        $this->assertArrayHasKey('General', $byCategory);
        $this->assertArrayHasKey('Navigation', $byCategory);
    }
}

// Test block implementation
class TestBlock implements BlockInterface
{
    public function getId(): string
    {
        return 'test_block';
    }
    
    public function getLabel(): string
    {
        return 'Test Block';
    }
    
    public function getDescription(): string
    {
        return 'A test block';
    }
    
    public function getModule(): string
    {
        return 'test_module';
    }
    
    public function getCategory(): string
    {
        return 'General';
    }
    
    public function render(Request $request, array $settings = []): string
    {
        return '<div>Test Block Content</div>';
    }
    
    public function getConfigForm(array $settings = []): array
    {
        return [];
    }
    
    public function validateConfig(array $settings): array
    {
        return [];
    }
    
    public function isCacheable(): bool
    {
        return true;
    }
    
    public function getCacheTags(): array
    {
        return ['test_block'];
    }
    
    public function getCacheContexts(): array
    {
        return [];
    }
}