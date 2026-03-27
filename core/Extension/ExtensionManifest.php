<?php

declare(strict_types=1);

namespace OOPress\Extension;

/**
 * ExtensionManifest — The parsed module.yaml or theme.yaml structure.
 * 
 * @api — This is a public data contract.
 */
class ExtensionManifest
{
    /**
     * @param array{
     *     name: string,
     *     id: string,
     *     description?: string,
     *     type: ExtensionType,
     *     version: string,
     *     stability: 'stable'|'beta'|'experimental',
     *     oopress: array{
     *         api: string,
     *         verified?: string
     *     },
     *     php?: array{minimum?: string},
     *     author?: array{name?: string, email?: string, url?: string},
     *     security?: array{contact?: string, policy?: string},
     *     license?: string,
     *     dependencies?: array{
     *         requires?: array<string, string>,
     *         suggests?: array<string, string>,
     *         conflicts?: array<string, string>
     *     },
     *     hooks?: string[],
     *     autoload?: array{psr4?: array<string, string>},
     *     regions?: array<string, string> // For themes only
     * } $data
     */
    private function __construct(
        public readonly string $name,
        public readonly string $id,
        public readonly ExtensionType $type,
        public readonly string $version,
        public readonly string $stability,
        public readonly array $oopress,
        public readonly ?string $description = null,
        public readonly ?array $php = null,
        public readonly ?array $author = null,
        public readonly ?array $security = null,
        public readonly ?string $license = null,
        public readonly ?array $dependencies = null,
        public readonly ?array $hooks = null,
        public readonly ?array $autoload = null,
        public readonly ?array $regions = null,
    ) {}

    /**
     * Create a manifest from a parsed YAML array.
     * 
     * @param array $data Parsed YAML data
     * @param ExtensionType $type Extension type
     * @return self
     * @throws \InvalidArgumentException if required fields are missing
     */
    public static function fromArray(array $data, ExtensionType $type): self
    {
        // Validate required fields
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Extension manifest missing required field: name');
        }
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Extension manifest missing required field: id');
        }
        if (empty($data['version'])) {
            throw new \InvalidArgumentException('Extension manifest missing required field: version');
        }
        if (empty($data['oopress']['api'])) {
            throw new \InvalidArgumentException('Extension manifest missing required field: oopress.api');
        }

        // Validate stability
        $stability = $data['stability'] ?? 'stable';
        if (!in_array($stability, ['stable', 'beta', 'experimental'], true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid stability value: %s. Must be stable, beta, or experimental', $stability)
            );
        }

        // Validate ID format (vendor/name)
        if (!preg_match('/^[a-z0-9_\-]+\/[a-z0-9_\-]+$/', $data['id'])) {
            throw new \InvalidArgumentException(
                sprintf('Invalid extension ID format: %s. Must be vendor/name', $data['id'])
            );
        }

        return new self(
            name: $data['name'],
            id: $data['id'],
            type: $type,
            version: $data['version'],
            stability: $stability,
            oopress: $data['oopress'],
            description: $data['description'] ?? null,
            php: $data['php'] ?? null,
            author: $data['author'] ?? null,
            security: $data['security'] ?? null,
            license: $data['license'] ?? null,
            dependencies: $data['dependencies'] ?? null,
            hooks: $data['hooks'] ?? null,
            autoload: $data['autoload'] ?? null,
            regions: $data['regions'] ?? null,
        );
    }

    /**
     * Check if the manifest declares a hook subscription.
     */
    public function hasHook(string $hookName): bool
    {
        return $this->hooks !== null && in_array($hookName, $this->hooks, true);
    }

    /**
     * Get the API compatibility constraint.
     */
    public function getApiConstraint(): string
    {
        return $this->oopress['api'];
    }

    /**
     * Get the author-verified date, if any.
     */
    public function getVerifiedDate(): ?string
    {
        return $this->oopress['verified'] ?? null;
    }

    /**
     * Check PHP version requirement.
     */
    public function isPhpVersionCompatible(string $phpVersion): bool
    {
        if ($this->php === null || empty($this->php['minimum'])) {
            return true;
        }

        return version_compare($phpVersion, $this->php['minimum'], '>=');
    }
}
