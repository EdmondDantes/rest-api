<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\ServiceManager\CommandDescriptorInterface;

class RouterDefaultStrategyTest extends TestCase
{
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
}
