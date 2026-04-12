<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Setting extends Model
{
    protected static string $table = 'settings';
    
    protected array $casts = [
        'id' => 'int',
        'setting_value' => 'string',
        'setting_type' => 'string',
        'setting_group' => 'string',
        'setting_order' => 'int'
    ];
    
    private static array $cache = [];
    
    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $setting = self::firstWhere(['setting_key' => $key]);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting->setting_value;
        
        // Cast based on type
        if ($setting->setting_type === 'checkbox') {
            $value = (bool)$value;
        } elseif ($setting->setting_type === 'number' || $setting->setting_type === 'text') {
            if (is_numeric($value)) {
                $value = (int)$value;
            }
        }
        
        self::$cache[$key] = $value;
        return $value;
    }
    
    /**
     * Set a setting value
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::firstWhere(['setting_key' => $key]);
        
        if ($setting) {
            $setting->setting_value = (string)$value;
            $result = $setting->save();
        } else {
            $setting = new self([
                'setting_key' => $key,
                'setting_value' => (string)$value,
                'setting_type' => 'text',
                'setting_group' => 'general',
                'setting_label' => $key
            ]);
            $result = $setting->save();
        }
        
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Get all settings grouped
     */
    public static function getAllGrouped(): array
    {
        $settings = self::query()->orderBy('setting_order', 'ASC')->get();
        $grouped = [];
        
        foreach ($settings as $setting) {
            if (!isset($grouped[$setting->setting_group])) {
                $grouped[$setting->setting_group] = [];
            }
            
            $value = $setting->setting_value;
            if ($setting->setting_type === 'checkbox') {
                $value = (bool)$value;
            }
            
            $grouped[$setting->setting_group][] = [
                'key' => $setting->setting_key,
                'label' => $setting->setting_label,
                'value' => $value,
                'type' => $setting->setting_type,
                'description' => $setting->setting_description,
                'options' => $setting->setting_options
            ];
        }
        
        return $grouped;
    }
    
    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}