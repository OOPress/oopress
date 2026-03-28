<?php

declare(strict_types=1);

namespace OOPress\Admin\Health;

use OOPress\Extension\ExtensionManifest;

/**
 * ModuleHealthInfo — Health information for a module.
 * 
 * @api
 */
class ModuleHealthInfo
{
    private bool $authorVerified = false;
    private ?string $authorVerifiedDate = null;
    private array $errors = [];
    private array $warnings = [];
    private array $securityAdvisories = [];
    
    public function __construct(
        public readonly string $moduleId,
        public readonly ExtensionManifest $manifest,
    ) {}
    
    public function setAuthorVerified(string $date): void
    {
        $this->authorVerified = true;
        $this->authorVerifiedDate = $date;
    }
    
    public function isAuthorVerified(): bool
    {
        return $this->authorVerified;
    }
    
    public function getAuthorVerifiedDate(): ?string
    {
        return $this->authorVerifiedDate;
    }
    
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }
    
    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }
    
    public function addSecurityAdvisory(array $advisory): void
    {
        $this->securityAdvisories[] = $advisory;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
    
    public function hasSecurityAdvisories(): bool
    {
        return !empty($this->securityAdvisories);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getWarnings(): array
    {
        return $this->warnings;
    }
    
    public function getSecurityAdvisories(): array
    {
        return $this->securityAdvisories;
    }
    
    public function getStatus(): string
    {
        if ($this->hasSecurityAdvisories()) {
            return 'security';
        }
        
        if ($this->hasErrors()) {
            return 'error';
        }
        
        if ($this->hasWarnings()) {
            return 'warning';
        }
        
        if (!$this->authorVerified) {
            return 'unverified';
        }
        
        return 'healthy';
    }
    
    public function getStatusLabel(): string
    {
        return match($this->getStatus()) {
            'healthy' => 'Healthy',
            'warning' => 'Warning',
            'error' => 'Error',
            'security' => 'Security Advisory',
            'unverified' => 'Unverified',
            default => 'Unknown',
        };
    }
    
    public function getStatusClass(): string
    {
        return match($this->getStatus()) {
            'healthy' => 'status-success',
            'warning' => 'status-warning',
            'error' => 'status-error',
            'security' => 'status-critical',
            'unverified' => 'status-muted',
            default => 'status-unknown',
        };
    }
}