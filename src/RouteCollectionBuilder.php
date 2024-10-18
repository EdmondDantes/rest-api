<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\DI\ContainerMutableInterface;
use IfCastle\Exceptions\LogicalException;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use IfCastle\TypeDefinitions\StringableInterface;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteCollectionBuilder
{
    public function __invoke(ContainerMutableInterface $systemEnvironment): void
    {
        $routeCollection            = $systemEnvironment->findDependency(RouteCollection::class);
        
        if($routeCollection instanceof RouteCollection) {
            return;
        }
        
        $systemEnvironment->set(
            RouteCollection::class, $this->buildRouteCollection($systemEnvironment->resolveDependency(ServiceLocatorInterface::class))
        );
    }
    
    protected function buildRouteCollection(ServiceLocatorInterface $serviceLocator): RouteCollection
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
        
        return $routeCollection;
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
            if($parameter instanceof StringableInterface && ($pattern = $parameter->getPattern()) !== null) {
                $requirements[$parameter->getName()] = $pattern;
            }
        }
        
        return $requirements;
    }
}