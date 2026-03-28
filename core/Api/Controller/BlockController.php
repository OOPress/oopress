<?php

declare(strict_types=1);

namespace OOPress\Api\Controller;

use OOPress\Block\BlockManager;
use OOPress\Block\RegionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * BlockController — Block API endpoints.
 * 
 * @api
 */
class BlockController extends ApiController
{
    public function __construct(
        private readonly BlockManager $blockManager,
        private readonly RegionManager $regionManager,
    ) {}
    
    /**
     * GET /api/v1/blocks
     * List all available blocks.
     */
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        // Only authenticated users can list blocks
        if (!$user) {
            return $this->error('Authentication required', 401);
        }
        
        $blocks = $this->blockManager->getAllBlockDefinitions();
        
        $data = [];
        foreach ($blocks as $id => $definition) {
            $data[] = [
                'id' => $definition->id,
                'label' => $definition->label,
                'description' => $definition->description,
                'category' => $definition->category,
                'module' => $definition->module,
                'cacheable' => $definition->cacheable,
            ];
        }
        
        return $this->success($data);
    }
    
    /**
     * GET /api/v1/blocks/regions
     * List all regions.
     */
    public function listRegions(Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        if (!$user) {
            return $this->error('Authentication required', 401);
        }
        
        $regions = $this->regionManager->getAllRegions();
        
        $data = [];
        foreach ($regions as $id => $region) {
            $data[] = [
                'id' => $region->id,
                'label' => $region->label,
                'description' => $region->description,
                'blocks' => $this->getBlocksForRegion($region->id),
            ];
        }
        
        return $this->success($data);
    }
    
    /**
     * GET /api/v1/blocks/regions/{region}
     * Get blocks for a specific region.
     */
    public function getRegion(string $region, Request $request): JsonResponse
    {
        $user = $this->getUser($request);
        
        if (!$user) {
            return $this->error('Authentication required', 401);
        }
        
        $regionDef = $this->regionManager->getRegion($region);
        
        if (!$regionDef) {
            return $this->error('Region not found', 404);
        }
        
        return $this->success([
            'id' => $regionDef->id,
            'label' => $regionDef->label,
            'description' => $regionDef->description,
            'blocks' => $this->getBlocksForRegion($region),
        ]);
    }
    
    /**
     * POST /api/v1/blocks/assign
     * Assign a block to a region.
     */
    public function assign(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        // Only admins can assign blocks
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->error('Access denied', 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $errors = $this->validate($data, [
            'block_id' => 'required',
            'region' => 'required',
        ]);
        
        if (!empty($errors)) {
            return $this->error('Validation failed', 422, $errors);
        }
        
        $this->blockManager->assignBlock(
            $data['block_id'],
            $data['region'],
            $data['weight'] ?? 0,
            $data['settings'] ?? []
        );
        
        return $this->success(null, 'Block assigned successfully');
    }
    
    /**
     * DELETE /api/v1/blocks/assign
     * Remove a block assignment.
     */
    public function unassign(Request $request): JsonResponse
    {
        $user = $this->requireAuth($request);
        
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->error('Access denied', 403);
        }
        
        $blockId = $request->query->get('block_id');
        $region = $request->query->get('region');
        
        if (!$blockId || !$region) {
            return $this->error('block_id and region are required', 422);
        }
        
        $this->blockManager->unassignBlock($blockId, $region);
        
        return $this->noContent();
    }
    
    private function getBlocksForRegion(string $region): array
    {
        $assignments = $this->blockManager->getBlocksForRegion($region);
        $blocks = [];
        
        foreach ($assignments as $assignment) {
            $definition = $this->blockManager->getBlockDefinition($assignment->blockId);
            if ($definition) {
                $blocks[] = [
                    'id' => $assignment->blockId,
                    'label' => $definition->label,
                    'weight' => $assignment->weight,
                    'settings' => $assignment->settings,
                    'status' => $assignment->status,
                ];
            }
        }
        
        return $blocks;
    }
}