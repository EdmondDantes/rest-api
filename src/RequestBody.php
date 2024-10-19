<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class RequestBody implements AttributeNameInterface
{
    public function __construct(
        public readonly array $mimeTypes = [],
    ) {}
    
    public function getAttributeName(): string
    {
        return 'RequestBody';
    }
}