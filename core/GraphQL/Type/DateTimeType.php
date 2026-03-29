<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ScalarType;
use GraphQL\Error\Error;

/**
 * DateTimeType — Custom scalar for ISO 8601 dates.
 * 
 * @internal
 */
class DateTimeType extends ScalarType
{
    public string $name = 'DateTime';
    public string $description = 'ISO 8601 datetime string';
    
    public function serialize($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }
        
        return (string) $value;
    }
    
    public function parseValue($value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }
        
        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $value);
        
        if (!$date) {
            throw new Error("Invalid datetime format: $value");
        }
        
        return $date;
    }
    
    public function parseLiteral($valueNode, ?array $variables = null): ?\DateTimeImmutable
    {
        if (!$valueNode instanceof \GraphQL\Language\AST\StringValueNode) {
            return null;
        }
        
        return $this->parseValue($valueNode->value);
    }
}