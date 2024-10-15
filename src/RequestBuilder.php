<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\Protocol\Exceptions\ParseException;
use IfCastle\Protocol\HeadersInterface;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\Protocol\RequestInterface;

final class RequestBuilder
{
    public function __invoke(RequestEnvironmentInterface $requestEnvironment): void
    {
        $httpRequest                = $requestEnvironment->findDependency(HttpRequestInterface::class);
        
        if($httpRequest instanceof HttpRequestInterface === false) {
            return;
        }
        
        $contentType                = $httpRequest->getHeader(HeadersInterface::CONTENT_TYPE)[0] ?? '';
        
        if($contentType === 'application/json') {
            
            $body                   = $httpRequest->getBody();
            
            try {
                $parameters         = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new ParseException('Failed to parse JSON request body', 0, $exception);
            }
        
            $requestEnvironment->set(
                RequestInterface::class,
                new RestRequest($httpRequest->getUri(), $httpRequest->getMethod(), $parameters, $httpRequest->getHeaders())
            );
            
        } elseif (in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data', true])) {
            
            $form                   = $httpRequest->retrieveRequestForm();
            
            // merge parameters from URL and body
            $parameters             = $httpRequest->requestParameters();
            
            if($form !== null) {
                $parameters         = array_merge($parameters, $form->post);
            }
            
            $requestEnvironment->set(
                RequestInterface::class,
                new RestRequest($httpRequest->getUri(), $httpRequest->getMethod(), $parameters, $httpRequest->getHeaders(), $form?->files ?? []));
        }
    }
}