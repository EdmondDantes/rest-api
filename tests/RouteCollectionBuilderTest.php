<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\Environment\SystemEnvironment;
use IfCastle\DI\ContainerMutableInterface;
use IfCastle\DI\Resolver;
use IfCastle\ServiceManager\DescriptorRepository;
use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\ServiceDescriptorBuilderByReflection;
use IfCastle\ServiceManager\ServiceLocator;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\TypeDefinitions\Resolver\ExplicitTypeResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RouteCollectionBuilderTest    extends TestCase
{
    public function testRouter(): void
    {
        $systemEnvironment          = $this->buildSystemEnvironment();
        $routerBuilder              = new RouteCollectionBuilder();
        
        $routerBuilder($systemEnvironment);
        
        $compiledRouteCollection    = $systemEnvironment->findDependency(CompiledRouteCollection::class);
        
        $this->assertInstanceOf(CompiledRouteCollection::class, $compiledRouteCollection, 'CompiledRouteCollection not found');
        
        $compiledUrlMatcher         = new CompiledUrlMatcher($compiledRouteCollection->collection, new RequestContext());
        
        $result                     = $compiledUrlMatcher->match('/base/some-method/some-string');
        
        $this->assertIsArray($result, 'Route not found');
        $this->assertArrayHasKey('_route', $result, 'Route not found');
        $this->assertEquals('someMethod', $result['_route'], 'Route not found');
        $this->assertArrayHasKey('id', $result, 'Parameter id is not found');
        $this->assertEquals('some-string', $result['id'], 'Parameter id is not equal to some-string');
        
        // Check _service parameter and _method parameter
        $this->assertArrayHasKey('_service', $result, 'Parameter _service is not found');
        $this->assertEquals('someService', $result['_service'], 'Parameter _service is not equal to someService');
        $this->assertArrayHasKey('_method', $result, 'Parameter _method is not found');
        $this->assertEquals('someMethod', $result['_method'], 'Parameter _method is not equal to someMethod');
    }
    
    protected function buildSystemEnvironment(): ContainerMutableInterface
    {
        $serviceConfig              = [
            'class'                 => SomeService::class,
            'isActive'              => true
        ];
        
        $repositoryReader           = $this->createMock(RepositoryReaderInterface::class);
        $repositoryReader->method('getServicesConfig')->willReturn(['someService' => $serviceConfig]);
        $repositoryReader->method('findServiceConfig')->willReturn($serviceConfig);
        
        $container                  = [];
        $descriptorRepository       = new DescriptorRepository(
            $repositoryReader,
            new ExplicitTypeResolver,
            new ServiceDescriptorBuilderByReflection
        );
        
        $container[DescriptorRepository::class] = $descriptorRepository;
        $container[ServiceLocatorInterface::class] = new ServiceLocator($descriptorRepository);
        
        return new SystemEnvironment(new Resolver, $container);
    }
}
