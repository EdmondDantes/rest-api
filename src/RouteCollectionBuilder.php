<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Exceptions\LogicalException;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use IfCastle\TypeDefinitions\StringableInterface;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteCollectionBuilder
{
    public function __construct(
        ServiceLocatorInterface $serviceLocator
    )
    {
        $this->buildRouteCollection($serviceLocator);
    }
    
    private function buildRouteCollection(ServiceLocatorInterface $serviceLocator): void
    {
        $routeCollection            = new RouteCollection();
        $serviceList                = $serviceLocator->getServiceList();
        
        foreach ($serviceList as $serviceName) {
            try {
                $serviceDescriptor  = $serviceLocator->getServiceDescriptor($serviceName);
                
                foreach ($serviceDescriptor->getServiceMethods() as $methodDescriptor) {
                    $routeDescriptor = $methodDescriptor->findAttribute(RouteAttribute::class);
                    
                    if ($routeDescriptor instanceof RouteAttribute === false) {
                        continue;
                    }
                    
                    $routeCollection->add(
                        $methodDescriptor->getName(),
                        $this->defineRoute($routeDescriptor, $methodDescriptor, $serviceName)
                    );
                }
                
            } catch (\Throwable) {
                // ignore
            }
        }
    }
    
    /**
     * @throws LogicalException
     */
    protected function defineRoute(RouteAttribute $routeAttribute, FunctionDescriptorInterface $methodDescriptor, string $serviceName): Route
    {
        $defaults                   = $routeAttribute->getDefaults();
        
        if(empty($defaults)) {
            $defaults               = $this->defineDefaults($methodDescriptor);
        }
        
        $route                      = new Route(
            $routeAttribute->getPath(),
            $defaults,
            $this->defineRequirements($methodDescriptor),
            $routeAttribute->getOptions(),
            $routeAttribute->getHost(),
            $routeAttribute->getSchemes(),
            $routeAttribute->getMethods(),
            $routeAttribute->getCondition()
        );
        
        $route->addDefaults([
            'service'               => $serviceName,
            'method'                => $methodDescriptor->getFunctionName()
        ]);
        
        return $route;
    }
    
    protected function defineDefaults(FunctionDescriptorInterface $methodDescriptor): array
    {
        $defaults                   = [];
        
        foreach ($methodDescriptor->getArguments() as $parameter) {
            if($parameter->isDefaultValueAvailable()) {
                $defaults[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }
        
        return $defaults;
    }
    
    /**
     * @throws LogicalException
     */
    protected function defineRequirements(FunctionDescriptorInterface $methodDescriptor): array
    {
        $requirements               = [];
        
        foreach ($methodDescriptor->getArguments() as $parameter) {
            if(false === $parameter instanceof StringableInterface) {
                throw new LogicalException([
                    'template'      => 'The parameter {parameter} of {class}.{method} must have a stringable interface for URL routing',
                    'parameter'     => $parameter->getName(),
                    'class'         => $methodDescriptor->getClassName(),
                    'method'        => $methodDescriptor->getFunctionName(),
                    'tags'          => ['route']
                ]);
            }
            
            $requirements[$parameter->getName()] = $parameter->getPattern() ?? '\w+';
        }
        
        return $requirements;
    }
    
}