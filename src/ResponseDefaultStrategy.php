<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;

class ResponseDefaultStrategy
{
    public function __invoke(RequestEnvironmentInterface $requestEnvironment): void
    {
        $response                   = $requestEnvironment->getResponse();
        
        if($response === null) {
            $response               = $requestEnvironment->getResponseFactory()->createResponse();
            $requestEnvironment->defineResponse($response);
        }
    }
}