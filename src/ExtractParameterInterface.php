<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

interface ExtractParameterInterface
{
    public function extractParameter(DefinitionInterface         $parameter,
                                     FunctionDescriptorInterface $methodDescriptor,
                                     array                       $rawParameters,
                                     array                       $routeParameters,
                                     RequestEnvironmentInterface $requestEnvironment
    ): mixed;
}