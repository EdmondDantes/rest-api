<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use Symfony\Component\Routing\RouteCollection;

class RouteCollectionBuilder
{
    public function buildRouteCollection(): void
    {
        $this->routeCollection      = new RouteCollection();
        
        $serviceList                = $this->serviceManager->getServiceList();
        $total                      = count($serviceList);
        $current                    = 0;
        
        foreach ($serviceList as $serviceName) {
            
            ++$current;
            
            try {
                $serviceDescriptor  = $this->serviceManager->getServiceDescriptor($serviceName);
                
                foreach ($serviceDescriptor->getServiceMethods() as $methodDescriptor) {
                    $routeDescriptor = $methodDescriptor->findAttribute(Rest::class);
                    
                    if ($routeDescriptor instanceof Rest === false) {
                        continue;
                    }
                    
                    $this->routeCollection->add(
                        $methodDescriptor->getMethod(),
                        $this->defineRoute($routeDescriptor, $methodDescriptor, $serviceName)
                    );
                }
                
                $progressDispatcher?->progressItemEnd($current, $total, Result::ok(), $serviceName);
            } catch (\Throwable $exception) {
                $progressDispatcher?->progressItemEnd($current, $total, new Result($exception), $serviceName);
            }
        }
    }
}