<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class Route                         extends \Symfony\Component\Routing\Attribute\Route
                                    implements AttributeNameInterface
{
    #[\Override]
    public function getAttributeName(): string
    {
        return 'Route';
    }
}