<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;

class CommandDescriptor implements CommandDescriptorInterface
{
    protected array|null $parameters = null;
    protected \WeakReference|null $httpRequest = null;
    
    public function __construct(
        public readonly string $serviceName,
        public readonly string $methodName,
        public readonly array $routeAttributes,
        HttpRequestInterface $httpRequest
    )
    {
        $this->httpRequest          = \WeakReference::create($httpRequest);
    }
    
    #[\Override]
    public function getServiceNamespace(): string
    {
        return '';
    }
    
    #[\Override]
    public function getServiceName(): string
    {
        return $this->serviceName;
    }
    
    #[\Override]
    public function getMethodName(): string
    {
        return $this->methodName;
    }
    
    #[\Override]
    public function getCommandName(): string
    {
        return $this->serviceName . '.' . $this->methodName;
    }
    
    #[\Override]
    public function getParameters(): array
    {
        if($this->parameters === null) {
            $this->parameters = $this->extractParameters();
        }
        
        return $this->parameters;
    }
    
    protected function extractParameters(): array
    {
        return $this->httpRequest->getParameters();
    }
}