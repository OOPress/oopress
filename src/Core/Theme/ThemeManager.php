<?php

declare(strict_types=1);

namespace OOPress\Core\Theme;

use OOPress\Models\Setting;

class ThemeManager
{
    private string $themesPath;
    private string $activeTheme;
    private array $themeData = [];
    
    public function __construct()
    {
        $this->themesPath = __DIR__ . '/../../../themes/';
        $this->activeTheme = Setting::get('active_theme', 'default');
        $this->loadThemeData();
    }
    
    private function loadThemeData(): void
    {
        $themePath = $this->themesPath . $this->activeTheme . '/theme.json';
        if (file_exists($themePath)) {
            $this->themeData = json_decode(file_get_contents($themePath), true);
        }
    }
    
    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }
    
    public function setActiveTheme(string $theme): bool
    {
        if ($this->themeExists($theme)) {
            Setting::set('active_theme', $theme);
            $this->activeTheme = $theme;
            $this->loadThemeData();
            return true;
        }
        return false;
    }
    
    public function themeExists(string $theme): bool
    {
        return is_dir($this->themesPath . $theme) && 
               file_exists($this->themesPath . $theme . '/theme.json');
    }
    
    public function getAllThemes(): array
    {
        $themes = [];
        $dirs = glob($this->themesPath . '*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $themeName = basename($dir);
            $themeFile = $dir . '/theme.json';
            if (file_exists($themeFile)) {
                $data = json_decode(file_get_contents($themeFile), true);
                $themes[] = [
                    'name' => $themeName,
                    'title' => $data['name'] ?? ucfirst($themeName),
                    'description' => $data['description'] ?? '',
                    'version' => $data['version'] ?? '1.0.0',
                    'author' => $data['author'] ?? 'OOPress',
                    'active' => $themeName === $this->activeTheme
                ];
            }
        }
        
        return $themes;
    }
    
    public function getThemeViewPath(): string
    {
        $themePath = $this->themesPath . $this->activeTheme . '/views/';
        if (is_dir($themePath)) {
            return $themePath;
        }
        // Fallback to default theme
        return $this->themesPath . 'default/views/';
    }
    
    public function getThemeAssetUrl(string $asset): string
    {
        return '/themes/' . $this->activeTheme . '/assets/' . ltrim($asset, '/');
    }
    
    public function getThemeData(): array
    {
        return $this->themeData;
    }
}