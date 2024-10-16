<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

interface ServiceCallDescriptorInterface
{
    public function getServiceName(): string;
    public function getMethodName(): string;
}