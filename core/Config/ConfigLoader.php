<?php

namespace OOPress\Config;

class ConfigLoader
{
    private array $config = [];
    private string $configPath;
    
    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->load();
    }
    
    private function load(): void
    {
        $this->config = [];
        
        // Load all PHP files in config directory
        $files = glob($this->configPath . '/*.php');
        
        foreach ($files as $file) {
            $data = require $file;
            if (is_array($data)) {
                $this->config = array_merge_recursive($this->config, $data);
            }
        }
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }
        
        $config = $value;
    }
    
    public function all(): array
    {
        return $this->config;
    }
    
    public function saveConfigFile(string $file, array $data): bool
    {
        // Merge with existing config for that file
        $existing = [];
        if (file_exists($file)) {
            $existing = require $file;
            if (is_array($existing)) {
                $data = array_merge($existing, $data);
            }
        }
        
        $content = '<?php' . "\n\nreturn " . var_export($data, true) . ";\n";
        $result = file_put_contents($file, $content) !== false;
        
        // Reload config after saving
        if ($result) {
            $this->load();
        }
        
        return $result;
    }
}