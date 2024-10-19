<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\DI\ContainerMutableInterface;
use IfCastle\Exceptions\LogicalException;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use IfCastle\TypeDefinitions\StringableInterface;
use IfCastle\RestApi\Route as RouteAttribute;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCompiler;

class RouteCollectionBuilder
{
    public function __invoke(ContainerMutableInterface $systemEnvironment): void
    {
        $routeCollection            = $systemEnvironment->findDependency(RouteCollection::class);
        
        if($routeCollection instanceof RouteCollection) {
            return;
        }
        
        $systemEnvironment->set(
            CompiledRouteCollection::class, $this->compile(
                $this->buildRouteCollection($systemEnvironment->resolveDependency(ServiceLocatorInterface::class))
            )
        );
    }
    
    protected function compile(RouteCollection $routeCollection): CompiledRouteCollection
    {
        return new CompiledRouteCollection((new CompiledUrlMatcherDumper($routeCollection))->getCompiledRoutes());
    }
    
    protected function buildRouteCollection(ServiceLocatorInterface $serviceLocator): RouteCollection
    {
        $routeCollection            = new RouteCollection();
        $serviceList                = $serviceLocator->getServiceList();
        
        foreach ($serviceList as $serviceName) {
            try {
                $serviceDescriptor  = $serviceLocator->getServiceDescriptor($serviceName);
                
                foreach ($serviceDescriptor->getServiceMethods() as $methodDescriptor) {
                    $routeAttribute = $methodDescriptor->findAttribute(RouteAttribute::class);
                    
                    if ($routeAttribute instanceof RouteAttribute === false) {
                        continue;
                    }
                    
                    $routeCollection->add(
                        $methodDescriptor->getName(),
                        $this->defineRoute($routeAttribute, $methodDescriptor, $serviceName)
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
        $route                      = new Route(
            $routeAttribute->getPath(),
            [],
            [],
            $routeAttribute->getOptions(),
            $routeAttribute->getHost(),
            $routeAttribute->getSchemes(),
            $routeAttribute->getMethods(),
            $routeAttribute->getCondition()
        );
        
        $parameters                 = RouteCompiler::compile($route)->getPathVariables();
        $requirements               = [];
        $founded                    = [];
        
        foreach ($methodDescriptor->getArguments() as $parameter) {
            
            $name                   = $parameter->getName();
            
            if(in_array($name, $parameters, true) === false) {
                continue;
            }
            
            if(false === $parameter instanceof StringableInterface) {
                throw new LogicalException([
                    'template'      => 'Route parameter {parameter} for {service}->{method} must implement StringableInterface',
                    'parameter'     => $name,
                    'service'       => $serviceName,
                    'method'        => $methodDescriptor->getFunctionName()
                ]);
            }
            
            if(($pattern = $parameter->getPattern()) !== null) {
                $requirements[$name] = $pattern;
            }
            
            $founded[]              = $name;
        }
        
        if(count($founded) !== count($parameters)) {
            throw new LogicalException([
                'template'          => 'Route parameters {parameters} for {service}->{method} are not defined in the method arguments',
                'parameters'        => implode(', ', array_diff($parameters, $founded)),
                'service'           => $serviceName,
                'method'            => $methodDescriptor->getFunctionName()
            ]);
        }
        
        $route->setRequirements($requirements);
        
        // add information about service and method
        $route->addDefaults([
            '_service'              => $serviceName,
            '_method'               => $methodDescriptor->getFunctionName()
        ]);
        
        return $route;
    }
}