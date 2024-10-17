<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\DesignPatterns\ExecutionPlan\StagePointer;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

final class RouterDefaultStrategy
{
    private RouteCollection|null $routeCollection = null;
    
    public function __invoke(RequestEnvironmentInterface $requestEnvironment): StagePointer|null
    {
        if($requestEnvironment->hasDependency(CommandDescriptorInterface::class)) {
            return new StagePointer(breakCurrent: true);
        }
        
        $httpRequest                = $requestEnvironment->findDependency(HttpRequestInterface::class);
        
        if($httpRequest === null) {
            return null;
        }
        
        if($this->routeCollection === null) {
            $this->buildRouteCollection($requestEnvironment);
        }
        
        $attributes                 = (new UrlMatcher($this->routeCollection, $this->defineRequestContext($httpRequest)))
            ->match($httpRequest->getUri()->getPath());
        
        if(empty($attributes['service']) || empty($attributes['method'])) {
            return null;
        }
        
        $requestEnvironment->set(CommandDescriptorInterface::class, new CommandDescriptor(
            serviceName:    $attributes['service'],
            methodName:     $attributes['method'],
            routeAttributes: $attributes,
            httpRequest:    $httpRequest
        ));
        
        return new StagePointer(breakCurrent: true);
    }
    
    private function buildRouteCollection(RequestEnvironmentInterface $requestEnvironment): void
    {
        $builder                    = new RouteCollectionBuilder;
        $builder($requestEnvironment->resolveDependency(ServiceLocatorInterface::class));
        
        $this->routeCollection      = $builder->getRouteCollection();
    }
    
    private function defineRequestContext(HttpRequestInterface $httpRequest): RequestContext
    {
        $uri                        = $httpRequest->getUri();
        
        return new RequestContext(
            baseUrl:        $uri->getPath(),
            method:         $httpRequest->getMethod(),
            host:           $uri->getHost(),
            scheme:         $uri->getScheme(),
            httpPort:       $uri->getPort(),
            queryString:    $uri->getQuery()
        );
    }
    
}