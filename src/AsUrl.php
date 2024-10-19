<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class AsUrl implements AttributeNameInterface
{
    #[\Override]
    public function getAttributeName(): string
    {
        return 'AsUrl';
    }
}