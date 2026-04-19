<?php

declare(strict_types=1);

namespace OOPress\Core\Plugin;

class PluginManager
{
    private string $pluginsPath;
    private array $activePlugins = [];
    private array $loadedPlugins = [];
    
    public function __construct()
    {
        $this->pluginsPath = __DIR__ . '/../../../plugins/';
        $this->loadActivePlugins();
    }
    
    private function loadActivePlugins(): void
    {
        $active = \OOPress\Models\Setting::get('active_plugins', []);
        $this->activePlugins = is_array($active) ? $active : [];
    }
    
    public function getActivePlugins(): array
    {
        return $this->activePlugins;
    }
    
    public function activatePlugin(string $slug): bool
    {
        if (!$this->pluginExists($slug)) {
            return false;
        }
        
        if (!in_array($slug, $this->activePlugins)) {
            $this->activePlugins[] = $slug;
            \OOPress\Models\Setting::set('active_plugins', $this->activePlugins);
            
            // Call plugin activation hook
            $this->loadPlugin($slug);
            Hook::doAction('plugin_activated_' . $slug);
            
            return true;
        }
        
        return false;
    }
    
    public function deactivatePlugin(string $slug): bool
    {
        if (in_array($slug, $this->activePlugins)) {
            // Call plugin deactivation hook
            Hook::doAction('plugin_deactivated_' . $slug);
            
            $this->activePlugins = array_diff($this->activePlugins, [$slug]);
            \OOPress\Models\Setting::set('active_plugins', $this->activePlugins);
            return true;
        }
        
        return false;
    }
    
    public function pluginExists(string $slug): bool
    {
        return is_dir($this->pluginsPath . $slug) && 
               file_exists($this->pluginsPath . $slug . '/plugin.php');
    }
    
    public function getAllPlugins(): array
    {
        $plugins = [];
        $dirs = glob($this->pluginsPath . '*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $slug = basename($dir);
            $pluginFile = $dir . '/plugin.php';
            $manifest = $dir . '/plugin.json';
            
            if (file_exists($pluginFile)) {
                $pluginData = [
                    'slug' => $slug,
                    'name' => $slug,
                    'description' => '',
                    'version' => '1.0.0',
                    'author' => 'Unknown',
                    'active' => in_array($slug, $this->activePlugins)
                ];
                
                if (file_exists($manifest)) {
                    $manifestData = json_decode(file_get_contents($manifest), true);
                    $pluginData = array_merge($pluginData, $manifestData);
                }
                
                $plugins[] = $pluginData;
            }
        }
        
        return $plugins;
    }
    
    public function loadPlugin(string $slug): bool
    {
        if (!$this->pluginExists($slug)) {
            return false;
        }
        
        if (in_array($slug, $this->loadedPlugins)) {
            return true;
        }
        
        $pluginFile = $this->pluginsPath . $slug . '/plugin.php';
        
        if (file_exists($pluginFile)) {
            // Define constants for the plugin
            define(strtoupper($slug) . '_PATH', $this->pluginsPath . $slug . '/');
            define(strtoupper($slug) . '_URL', '/plugins/' . $slug . '/');
            
            require_once $pluginFile;
            $this->loadedPlugins[] = $slug;
            
            Hook::doAction('plugin_loaded_' . $slug);
            return true;
        }
        
        return false;
    }
    
    public function loadActivePluginsFromDB(): void
    {
        foreach ($this->activePlugins as $slug) {
            $this->loadPlugin($slug);
        }
    }
    
    public function getPluginData(string $slug): array
    {
        $manifest = $this->pluginsPath . $slug . '/plugin.json';
        if (file_exists($manifest)) {
            return json_decode(file_get_contents($manifest), true);
        }
        return [];
    }
}