<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;

final class Bootloader implements BootloaderInterface
{
    #[\Override]
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void
    {
        $bootloaderExecutor->getBootloaderContext()->getRequestEnvironmentPlan()
                                                   ->addBuildHandler(new RequestBuilder)
                                                   ->addDispatchHandler(new RouterDefaultStrategy)
                                                   ->addResponseHandler(new ResponseDefaultStrategy)
                                                   ->addFinallyHandler(new ErrorDefaultStrategy);
        
        $bootloaderExecutor->getBootloaderContext()->getSystemEnvironmentBootBuilder()
                                                   ->bindConstructible(RouterInterface::class, Router::class);
    }
}