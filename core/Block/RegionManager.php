<?php

declare(strict_types=1);

namespace OOPress\Block;

use OOPress\Extension\ExtensionLoader;
use OOPress\Path\PathResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * RegionManager — Manages theme regions.
 * 
 * @api
 */
class RegionManager
{
    /**
     * @var array<string, RegionDefinition>
     */
    private array $regions = [];
    
    /**
     * @var array<string, string> Region assignments by theme
     */
    private array $themeRegions = [];
    
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
        private readonly PathResolver $pathResolver,
    ) {
        $this->discoverRegions();
    }
    
    /**
     * Discover regions from themes.
     */
    public function discoverRegions(): void
    {
        foreach ($this->extensionLoader->getThemes() as $themeId => $theme) {
            $this->discoverThemeRegions($themeId);
        }
    }
    
    /**
     * Discover regions from a theme's theme.yaml.
     */
    private function discoverThemeRegions(string $themeId): void
    {
        $themePath = $this->pathResolver->getThemePath($themeId);
        $themeFile = $themePath . '/theme.yaml';
        
        if (!file_exists($themeFile)) {
            return;
        }
        
        $yaml = file_get_contents($themeFile);
        $data = Yaml::parse($yaml);
        
        if (!is_array($data)) {
            return;
        }
        
        $regions = $data['regions'] ?? [];
        
        foreach ($regions as $regionId => $regionData) {
            if (is_string($regionData)) {
                $regionData = ['label' => $regionData];
            }
            
            $region = RegionDefinition::fromArray($regionId, $regionData);
            $this->regions[$regionId] = $region;
            $this->themeRegions[$themeId][] = $regionId;
        }
    }
    
    /**
     * Get a region definition.
     */
    public function getRegion(string $regionId): ?RegionDefinition
    {
        return $this->regions[$regionId] ?? null;
    }
    
    /**
     * Get all regions.
     * 
     * @return array<string, RegionDefinition>
     */
    public function getAllRegions(): array
    {
        return $this->regions;
    }
    
    /**
     * Get regions for a theme.
     * 
     * @return array<string, RegionDefinition>
     */
    public function getRegionsForTheme(string $themeId): array
    {
        $regionIds = $this->themeRegions[$themeId] ?? [];
        $regions = [];
        
        foreach ($regionIds as $regionId) {
            if (isset($this->regions[$regionId])) {
                $regions[$regionId] = $this->regions[$regionId];
            }
        }
        
        return $regions;
    }
    
    /**
     * Get regions grouped by theme.
     * 
     * @return array<string, array<string, RegionDefinition>>
     */
    public function getRegionsByTheme(): array
    {
        $grouped = [];
        
        foreach ($this->themeRegions as $themeId => $regionIds) {
            $grouped[$themeId] = [];
            foreach ($regionIds as $regionId) {
                if (isset($this->regions[$regionId])) {
                    $grouped[$themeId][$regionId] = $this->regions[$regionId];
                }
            }
        }
        
        return $grouped;
    }
}
