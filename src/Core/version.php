<?php

declare(strict_types=1);

namespace OOPress\Core;

class Version
{
    public const VERSION = '1.0.0';
    public const NAME = 'OOPress';
    public const DESCRIPTION = 'Lean, modern PHP CMS with clean OOP architecture';
    public const AUTHOR = 'OOPress Team';
    public const LICENSE = 'Apache-2.0';
    public const HOMEPAGE = 'https://oopress.org';
    
    /**
     * Get the full version string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }
    
    /**
     * Get the application name
     */
    public static function getName(): string
    {
        return self::NAME;
    }
    
    /**
     * Get the application description
     */
    public static function getDescription(): string
    {
        return self::DESCRIPTION;
    }
    
    /**
     * Get the author
     */
    public static function getAuthor(): string
    {
        return self::AUTHOR;
    }
    
    /**
     * Get the license
     */
    public static function getLicense(): string
    {
        return self::LICENSE;
    }
    
    /**
     * Get the homepage URL
     */
    public static function getHomepage(): string
    {
        return self::HOMEPAGE;
    }
    
    /**
     * Get version information as array
     */
    public static function getInfo(): array
    {
        return [
            'name' => self::NAME,
            'version' => self::VERSION,
            'description' => self::DESCRIPTION,
            'author' => self::AUTHOR,
            'license' => self::LICENSE,
            'homepage' => self::HOMEPAGE
        ];
    }
}
