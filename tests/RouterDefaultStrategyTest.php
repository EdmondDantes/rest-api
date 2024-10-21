<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\Environment\SystemEnvironment;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestEnvironment;
use IfCastle\DI\Resolver;
use IfCastle\Protocol\HeadersInterface;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\DescriptorRepository;
use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\ServiceDescriptorBuilderByReflection;
use IfCastle\ServiceManager\ServiceLocator;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\TypeDefinitions\Resolver\ExplicitTypeResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class RouterDefaultStrategyTest extends TestCase
{
    protected SystemEnvironmentInterface|null $systemEnvironment = null;
    
    public function testRouter(): void
    {
        $requestEnvironment         = $this->buildRequestEnvironment('/base/some-method/some-string');
        $routerDefaultStrategy      = new RouterDefaultStrategy;
        
        $routerDefaultStrategy($requestEnvironment);
    
        $command                    = $requestEnvironment->findDependency(CommandDescriptorInterface::class);
        
        $this->assertNotNull($command, 'Command not found');
        $this->assertEquals('someService', $command->getServiceName(), 'Service name is not equal to someService');
        $this->assertEquals('someMethod', $command->getMethodName(), 'Method name is not equal to someMethod');
        $this->assertEquals(['id' => 'some-string'], $command->getParameters(), 'Parameters are not equal');
    }
    
    #[\Override]
    protected function tearDown(): void
    {
        $this->systemEnvironment?->dispose();
        $this->systemEnvironment = null;
    }
    
    protected function buildRequestEnvironment(
        string $url,
        string $method              = 'GET',
        string $contentType         = 'application/json',
        string $body                = ''
    ): RequestEnvironment
    {
        $systemEnvironment          = $this->buildSystemEnvironment();
        $this->systemEnvironment    = $systemEnvironment;
        
        $httpRequest                = $this->createMock(HttpRequestInterface::class);
        $httpRequest->method('getMethod')->willReturn($method);
        
        $uri                        = $this->createMock(UriInterface::class);
        
        $uri->method('getPath')->willReturn($url);
        $uri->method('getHost')->willReturn('localhost');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn(80);
        $uri->method('getQuery')->willReturn('some-query');
        
        $httpRequest->method('getUri')->willReturn($uri);
        
        $httpRequest->method('getHeader')->with(HeadersInterface::CONTENT_TYPE)->willReturn([$contentType]);
        $httpRequest->method('getBody')->willReturn($body);
        
        
        $env                        = new RequestEnvironment($httpRequest, parentContainer: $systemEnvironment);
        $env->set(HttpRequestInterface::class, $httpRequest);
        
        return $env;
    }
    
    protected function buildSystemEnvironment(): SystemEnvironmentInterface
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
