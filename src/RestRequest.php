<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Protocol\Request;
use Psr\Http\Message\UriInterface as PsrUri;

final class RestRequest             extends Request
{
    public function __construct(PsrUri $uri, string $method, array $request, array $headers, array $files = [])
    {
        $this->uri                  = $uri;
        $this->method               = $method;
        $this->parameters           = $request;
        $this->headers              = $headers;
        $this->uploadedFiles        = $files;
    }
}