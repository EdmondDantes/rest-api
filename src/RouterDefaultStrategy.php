<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\DesignPatterns\ExecutionPlan\StagePointer;
use IfCastle\Protocol\Http\HttpRequestInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

final class RouterDefaultStrategy
{
    private RouteCollection|null $routeCollection = null;
    
    public static function callServiceMethod(string $serviceName, string $methodName, RequestEnvironmentInterface $requestEnvironment): mixed
    {
    
    }
    
    public function __invoke(RequestEnvironmentInterface $requestEnvironment): StagePointer|null
    {
        if($requestEnvironment->hasDependency(ActionInterface::class)) {
            return new StagePointer(breakCurrent: true);
        }
        
        $httpRequest                = $requestEnvironment->findDependency(HttpRequestInterface::class);
        
        if($httpRequest === null) {
            return null;
        }
        
        if($this->routeCollection === null) {
            $this->buildRouteCollection();
        }
        
        $attributes                 = (new UrlMatcher($this->routeCollection, $this->defineRequestContext($httpRequest)))
            ->match($httpRequest->getUri()->getPath());
        
        if(empty($attributes['service']) || empty($attributes['method'])) {
            return null;
        }
        
        $requestEnvironment->set(ActionInterface::class, new ServiceCall(
            handler:        self::callServiceMethod(...),
            serviceName:    $attributes['service'],
            serviceMethod:  $attributes['method']
        ));
        
        return new StagePointer(breakCurrent: true);
    }
    
    private function buildRouteCollection(): void
    {
    
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