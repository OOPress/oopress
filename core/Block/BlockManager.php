<?php

declare(strict_types=1);

namespace OOPress\Block;

use Doctrine\DBAL\Connection;
use OOPress\Event\HookDispatcher;
use OOPress\Extension\ExtensionLoader;
use OOPress\Path\PathResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * BlockManager — Discovers and manages blocks.
 * 
 * @api
 */
class BlockManager
{
    /**
     * @var array<string, BlockDefinition>
     */
    private array $blockDefinitions = [];
    
    /**
     * @var array<string, BlockInstance>
     */
    private array $blockInstances = [];
    
    /**
     * @var array<string, array<BlockAssignment>>
     */
    private array $regionAssignments = [];
    
    private array $errors = [];
    
    public function __construct(
        private readonly Connection $connection,
        private readonly ExtensionLoader $extensionLoader,
        private readonly PathResolver $pathResolver,
        private readonly HookDispatcher $hookDispatcher,
    ) {
        $this->discoverBlocks();
        $this->loadAssignments();
    }
    
    /**
     * Discover blocks from all modules.
     */
    public function discoverBlocks(): void
    {
        // Discover from module block definitions
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $this->discoverModuleBlocks($moduleId);
        }
        
        // Dispatch event for modules to register blocks programmatically
        $event = new Event\BlockDiscoveryEvent($this);
        $this->hookDispatcher->dispatch($event, 'block.discover');
    }
    
    /**
     * Discover blocks from a module's block definitions file.
     */
    private function discoverModuleBlocks(string $moduleId): void
    {
        $blocksFile = $this->pathResolver->getModulePath($moduleId) . '/blocks.yaml';
        
        if (!file_exists($blocksFile)) {
            return;
        }
        
        $yaml = file_get_contents($blocksFile);
        $data = Yaml::parse($yaml);
        
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data as $blockId => $definition) {
            $definition['module'] = $moduleId;
            $fullId = $moduleId . '/' . $blockId;
            
            try {
                $this->registerBlock(BlockDefinition::fromArray($fullId, $definition));
            } catch (\Exception $e) {
                $this->errors[] = sprintf(
                    'Failed to register block %s: %s',
                    $fullId,
                    $e->getMessage()
                );
            }
        }
    }
    
    /**
     * Register a block definition.
     */
    public function registerBlock(BlockDefinition $definition): void
    {
        $this->blockDefinitions[$definition->id] = $definition;
    }
    
    /**
     * Get a block definition.
     */
    public function getBlockDefinition(string $id): ?BlockDefinition
    {
        return $this->blockDefinitions[$id] ?? null;
    }
    
    /**
     * Get all block definitions.
     * 
     * @return array<string, BlockDefinition>
     */
    public function getAllBlockDefinitions(): array
    {
        return $this->blockDefinitions;
    }
    
    /**
     * Get blocks by category.
     * 
     * @return array<string, array<BlockDefinition>>
     */
    public function getBlocksByCategory(): array
    {
        $grouped = [];
        
        foreach ($this->blockDefinitions as $id => $definition) {
            $category = $definition->category;
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$id] = $definition;
        }
        
        ksort($grouped);
        return $grouped;
    }
    
    /**
     * Load block assignments from database.
     */
    private function loadAssignments(): void
    {
        try {
            $schemaManager = $this->connection->createSchemaManager();
            if (!$schemaManager->tablesExist(['oop_block_assignments'])) {
                return;
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet
            return;
        }
        
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('oop_block_assignments')
            ->orderBy('region', 'ASC')
            ->addOrderBy('weight', 'ASC');
        
        $rows = $query->executeQuery()->fetchAllAssociative();
        
        foreach ($rows as $row) {
            $assignment = BlockAssignment::fromArray($row);
            $this->regionAssignments[$assignment->region][] = $assignment;
            
            // Create block instance
            $definition = $this->getBlockDefinition($assignment->blockId);
            if ($definition) {
                $this->blockInstances[$assignment->blockId] = $definition->createInstance();
            }
        }
    }
    
    /**
     * Get blocks assigned to a region.
     * 
     * @return array<BlockAssignment>
     */
    public function getBlocksForRegion(string $region): array
    {
        return $this->regionAssignments[$region] ?? [];
    }
    
    /**
     * Assign a block to a region.
     */
    public function assignBlock(string $blockId, string $region, int $weight = 0, array $settings = []): void
    {
        // Check if assignment already exists
        $exists = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('oop_block_assignments')
            ->where('block_id = :block_id')
            ->andWhere('region = :region')
            ->setParameter('block_id', $blockId)
            ->setParameter('region', $region)
            ->executeQuery()
            ->fetchOne();
        
        if ($exists) {
            $this->connection->update(
                'oop_block_assignments',
                ['weight' => $weight, 'settings' => json_encode($settings)],
                ['block_id' => $blockId, 'region' => $region]
            );
        } else {
            $this->connection->insert('oop_block_assignments', [
                'block_id' => $blockId,
                'region' => $region,
                'weight' => $weight,
                'settings' => json_encode($settings),
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        // Reload assignments
        $this->loadAssignments();
    }
    
    /**
     * Remove a block assignment.
     */
    public function unassignBlock(string $blockId, string $region): void
    {
        $this->connection->delete(
            'oop_block_assignments',
            ['block_id' => $blockId, 'region' => $region]
        );
        
        // Reload assignments
        $this->loadAssignments();
    }
    
    /**
     * Update block assignment settings.
     */
    public function updateAssignmentSettings(string $blockId, string $region, array $settings): void
    {
        $this->connection->update(
            'oop_block_assignments',
            ['settings' => json_encode($settings)],
            ['block_id' => $blockId, 'region' => $region]
        );
        
        // Reload assignments
        $this->loadAssignments();
    }
    
    /**
     * Update block weight (ordering).
     */
    public function updateBlockWeight(string $blockId, string $region, int $weight): void
    {
        $this->connection->update(
            'oop_block_assignments',
            ['weight' => $weight],
            ['block_id' => $blockId, 'region' => $region]
        );
        
        // Reload assignments
        $this->loadAssignments();
    }
    
    /**
     * Render a region.
     * 
     * @return string Rendered HTML of all blocks in the region
     */
    public function renderRegion(string $region, Request $request): string
    {
        $assignments = $this->getBlocksForRegion($region);
        
        if (empty($assignments)) {
            return '';
        }
        
        $output = '';
        
        foreach ($assignments as $assignment) {
            $block = $this->renderBlock($assignment, $request);
            if ($block) {
                $output .= $block;
            }
        }
        
        return $output;
    }
    
    /**
     * Render a specific block.
     */
    public function renderBlock(BlockAssignment $assignment, Request $request): ?string
    {
        $definition = $this->getBlockDefinition($assignment->blockId);
        
        if (!$definition) {
            return null;
        }
        
        $block = $this->blockInstances[$assignment->blockId] ?? null;
        
        if (!$block) {
            $block = $definition->createInstance();
            $this->blockInstances[$assignment->blockId] = $block;
        }
        
        // Check visibility rules
        if (!$this->checkVisibility($assignment, $request)) {
            return null;
        }
        
        try {
            $content = $block->render($request, $assignment->settings);
            
            if (empty($content)) {
                return null;
            }
            
            // Wrap block in standard markup
            $wrapper = $assignment->settings['wrapper'] ?? 'div';
            $classes = $assignment->settings['classes'] ?? 'block block-' . str_replace('/', '-', $assignment->blockId);
            
            return sprintf(
                '<%s class="%s" id="block-%s">%s</%s>',
                $wrapper,
                $classes,
                str_replace('/', '-', $assignment->blockId),
                $content,
                $wrapper
            );
            
        } catch (\Exception $e) {
            $this->errors[] = sprintf(
                'Failed to render block %s: %s',
                $assignment->blockId,
                $e->getMessage()
            );
            
            return null;
        }
    }
    
    /**
     * Check block visibility rules.
     */
    private function checkVisibility(BlockAssignment $assignment, Request $request): bool
    {
        $settings = $assignment->settings;
        
        // Check status
        if (!($assignment->status ?? true)) {
            return false;
        }
        
        // Check visibility by path
        if (!empty($settings['visibility_paths'])) {
            $currentPath = $request->getPathInfo();
            $match = false;
            
            foreach ($settings['visibility_paths'] as $path) {
                if ($this->pathMatches($currentPath, $path)) {
                    $match = true;
                    break;
                }
            }
            
            $visibilityType = $settings['visibility_paths_type'] ?? 'show';
            
            if ($visibilityType === 'show' && !$match) {
                return false;
            }
            
            if ($visibilityType === 'hide' && $match) {
                return false;
            }
        }
        
        // Check visibility by user role
        if (!empty($settings['visibility_roles'])) {
            // This will be expanded when we have user roles
            $userRoles = $request->getSession()->get('user_roles', ['anonymous']);
            $hasRole = !empty(array_intersect($userRoles, $settings['visibility_roles']));
            
            if (!$hasRole) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if a path matches a pattern.
     */
    private function pathMatches(string $currentPath, string $pattern): bool
    {
        // Convert pattern to regex
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        return (bool) preg_match($pattern, $currentPath);
    }
    
    /**
     * Get all errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
