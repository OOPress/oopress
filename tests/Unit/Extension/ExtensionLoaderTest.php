<?php

declare(strict_types=1);

namespace OOPress\Tests\Unit\Extension;

use OOPress\Tests\TestCase;
use OOPress\Extension\ExtensionLoader;
use OOPress\Extension\ExtensionType;
use OOPress\Path\PathResolver;

/**
 * Test ExtensionLoader functionality.
 * 
 * @internal
 */
class ExtensionLoaderTest extends TestCase
{
    private ExtensionLoader $loader;
    private string $testModulesDir;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test directories
        $this->testModulesDir = sys_get_temp_dir() . '/oopress_test_modules';
        $this->createTestDirectories();
        
        $pathResolver = $this->createMock(PathResolver::class);
        $pathResolver->method('getModulesPath')->willReturn($this->testModulesDir);
        $pathResolver->method('getThemesPath')->willReturn($this->testModulesDir);
        
        $this->loader = new ExtensionLoader($pathResolver, true);
    }
    
    protected function tearDown(): void
    {
        $this->removeTestDirectories();
        parent::tearDown();
    }
    
    private function createTestDirectories(): void
    {
        if (!is_dir($this->testModulesDir)) {
            mkdir($this->testModulesDir, 0755, true);
        }
        
        // Create test module
        $moduleDir = $this->testModulesDir . '/testmodule';
        mkdir($moduleDir);
        
        file_put_contents($moduleDir . '/module.yaml', <<<YAML
name: Test Module
id: test/testmodule
description: A test module
type: module
version: 1.0.0
stability: stable
oopress:
  api: "^1.0"
YAML
        );
    }
    
    private function removeTestDirectories(): void
    {
        if (is_dir($this->testModulesDir)) {
            $this->recursiveDelete($this->testModulesDir);
        }
    }
    
    private function recursiveDelete(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        
        rmdir($path);
    }
    
    public function testDiscoverModules(): void
    {
        $modules = $this->loader->getModules();
        
        $this->assertIsArray($modules);
        $this->assertArrayHasKey('test/testmodule', $modules);
    }
    
    public function testGetModule(): void
    {
        $module = $this->loader->getModule('test/testmodule');
        
        $this->assertNotNull($module);
        $this->assertEquals('Test Module', $module->name);
        $this->assertEquals('test/testmodule', $module->id);
        $this->assertEquals('1.0.0', $module->version);
    }
    
    public function testGetNonExistentModule(): void
    {
        $module = $this->loader->getModule('nonexistent/module');
        
        $this->assertNull($module);
    }
    
    public function testHasExtension(): void
    {
        $hasModule = $this->loader->hasExtension('test/testmodule', ExtensionType::Module);
        $hasTheme = $this->loader->hasExtension('nonexistent', ExtensionType::Theme);
        
        $this->assertTrue($hasModule);
        $this->assertFalse($hasTheme);
    }
    
    public function testGetModules(): void
    {
        $modules = $this->loader->getModules();
        
        $this->assertIsArray($modules);
        $this->assertNotEmpty($modules);
    }
    
    public function testManifestValidation(): void
    {
        $module = $this->loader->getModule('test/testmodule');
        
        $this->assertTrue($module->isPhpVersionCompatible(PHP_VERSION));
        $this->assertEquals('^1.0', $module->getApiConstraint());
    }
}