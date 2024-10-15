<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\Protocol\Request;

class RestRequest extends Request
{
    public function __construct(array $request, array $headers, array $files = [])
    {
        $this->parameters           = $request;
        $this->headers              = $headers;
        $this->uploadedFiles        = $files;
    }
}