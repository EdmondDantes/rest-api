<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;

final class ServiceCall             implements ActionInterface
{
    private readonly mixed $handler;
    
    public function __construct(callable $handler, public readonly string $serviceName, public readonly string $serviceMethod)
    {
        $this->handler              = $handler;
    }
    
    #[\Override]
    public function invoke(RequestEnvironmentInterface $requestEnvironment): mixed
    {
        return ($this->handler)($this->serviceName, $this->serviceMethod, $requestEnvironment);
    }
}