<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\DesignPatterns\ExecutionPlan\StagePointer;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

final class RouterDefaultStrategy
{
    private RouteCollection|null $routeCollection = null;
    
    public function __invoke(RequestEnvironmentInterface $container): StagePointer|null
    {
        if($container->hasDependency(CommandDescriptorInterface::class)) {
            return new StagePointer(breakCurrent: true);
        }
        
        $httpRequest                = $container->findDependency(HttpRequestInterface::class);
        
        if($httpRequest === null) {
            return null;
        }
        
        if($this->routeCollection === null) {
            $this->buildRouteCollection($container);
        }
        
        $attributes                 = (new UrlMatcher($this->routeCollection, $this->defineRequestContext($httpRequest)))
            ->match($httpRequest->getUri()->getPath());
        
        if(empty($attributes['service']) || empty($attributes['method'])) {
            return null;
        }
        
        $container->set(CommandDescriptorInterface::class, new CommandDescriptor(
            serviceName:    $attributes['service'],
            methodName:     $attributes['method'],
            routeAttributes: $attributes,
            httpRequest:    $httpRequest
        ));
        
        return new StagePointer(breakCurrent: true);
    }
    
    private function buildRouteCollection(RequestEnvironmentInterface $requestEnvironment): void
    {
        $routerCollection           = $requestEnvironment->findDependency(RouteCollection::class);
        
        if($routerCollection instanceof RouteCollection) {
            $this->routeCollection  = $routerCollection;
            return;
        }
        
        $builder                    = new RouteCollectionBuilder;
        $builder($requestEnvironment->getSystemEnvironment());
        
        $this->routeCollection      = $requestEnvironment->resolveDependency(RouteCollection::class);
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