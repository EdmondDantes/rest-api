<?php

declare(strict_types=1);

namespace IfCastle\RestApi;

final class CompiledRouteCollection
{
    public function __construct(public array $collection) {}
}
