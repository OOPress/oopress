<?php

declare(strict_types=1);

namespace OOPress\Extension;

/**
 * ExtensionType — Discriminated union for extension types.
 * 
 * @api
 */
enum ExtensionType: string
{
    case Module = 'module';
    case Theme = 'theme';
}
