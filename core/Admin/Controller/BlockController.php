<?php

declare(strict_types=1);

namespace OOPress\Admin\Controller;

use OOPress\Block\BlockManager;
use OOPress\Block\RegionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * BlockController — Block management pages.
 * 
 * @internal
 */
class BlockController
{
    public function __construct(
        private readonly BlockManager $blockManager,
        private readonly RegionManager $regionManager,
    ) {}
    
    /**
     * Block layout page.
     */
    public function layout(Request $request): Response
    {
        $regions = $this->regionManager->getAllRegions();
        $blocksByRegion = [];
        
        foreach ($regions as $regionId => $region) {
            $blocksByRegion[$regionId] = [
                'region' => $region,
                'blocks' => $this->blockManager->getBlocksForRegion($regionId),
            ];
        }
        
        // Get unassigned blocks
        $allBlocks = $this->blockManager->getAllBlockDefinitions();
        $assignedBlocks = [];
        
        foreach ($blocksByRegion as $regionData) {
            foreach ($regionData['blocks'] as $assignment) {
                $assignedBlocks[] = $assignment->blockId;
            }
        }
        
        $unassignedBlocks = array_filter(
            $allBlocks,
            fn($id) => !in_array($id, $assignedBlocks),
            ARRAY_FILTER_USE_KEY
        );
        
        $content = $this->renderTemplate('admin/blocks/layout.html.twig', [
            'regions' => $blocksByRegion,
            'unassigned_blocks' => $unassignedBlocks,
        ]);
        
        return new Response($content);
    }
    
    /**
     * Assign block to region.
     */
    public function assign(Request $request): Response
    {
        $blockId = $request->get('block');
        $region = $request->get('region');
        
        if (!$blockId || !$region) {
            return new RedirectResponse('/admin/structure/blocks');
        }
        
        $this->blockManager->assignBlock($blockId, $region);
        
        return new RedirectResponse('/admin/structure/blocks');
    }
    
    /**
     * Remove block assignment.
     */
    public function remove(Request $request): Response
    {
        $blockId = $request->get('block');
        $region = $request->get('region');
        
        if ($blockId && $region) {
            $this->blockManager->unassignBlock($blockId, $region);
        }
        
        return new RedirectResponse('/admin/structure/blocks');
    }
    
    /**
     * Configure block.
     */
    public function configure(string $blockId, Request $request): Response
    {
        $definition = $this->blockManager->getBlockDefinition($blockId);
        
        if (!$definition) {
            return new Response('Block not found', Response::HTTP_NOT_FOUND);
        }
        
        $block = $definition->createInstance();
        $assignments = $this->findAssignments($blockId);
        
        $form = $block->getConfigForm($assignments[0]->settings ?? []);
        
        $content = $this->renderTemplate('admin/blocks/configure.html.twig', [
            'block' => $definition,
            'form' => $form,
            'assignments' => $assignments,
        ]);
        
        return new Response($content);
    }
    
    /**
     * Update block order.
     */
    public function order(Request $request): Response
    {
        $region = $request->get('region');
        $order = $request->get('order', []);
        
        if ($region && is_array($order)) {
            foreach ($order as $weight => $blockId) {
                $this->blockManager->updateBlockWeight($blockId, $region, $weight);
            }
        }
        
        return new RedirectResponse('/admin/structure/blocks');
    }
    
    /**
     * Find block assignments.
     */
    private function findAssignments(string $blockId): array
    {
        $assignments = [];
        $regions = $this->regionManager->getAllRegions();
        
        foreach ($regions as $regionId => $region) {
            $blocks = $this->blockManager->getBlocksForRegion($regionId);
            foreach ($blocks as $assignment) {
                if ($assignment->blockId === $blockId) {
                    $assignments[] = $assignment;
                }
            }
        }
        
        return $assignments;
    }
    
    /**
     * Render a template.
     */
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use Twig when integrated
        return '<h1>Block Management</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}