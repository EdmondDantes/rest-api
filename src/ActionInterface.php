<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;

interface ActionInterface
{
    public function invoke(RequestEnvironmentInterface $requestEnvironment): mixed;
}