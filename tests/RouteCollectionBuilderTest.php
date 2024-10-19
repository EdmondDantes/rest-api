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

class RouteCollectionBuilderTest    extends TestCase
{
    public function testRouter(): void
    {
        $systemEnvironment          = $this->buildSystemEnvironment();
        $routerBuilder              = new RouteCollectionBuilder();
        
        $routerBuilder($systemEnvironment);
        
        $CompiledRouteCollection    = $systemEnvironment->findDependency(CompiledRouteCollection::class);
        
        $this->assertInstanceOf(CompiledRouteCollection::class, $CompiledRouteCollection, 'CompiledRouteCollection not found');
    }
    
    protected function buildSystemEnvironment(): ContainerMutableInterface
    {
        $repositoryReader           = $this->createMock(RepositoryReaderInterface::class);
        $repositoryReader->method('getServicesConfig')->willReturn([]);
        $repositoryReader->method('findServiceConfig')->willReturn([]);
        
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
