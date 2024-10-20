<?php
declare(strict_types=1);

namespace IfCastle\RestApi;

use IfCastle\ServiceManager\AsServiceMethod;

#[Rest('/base')]
final class SomeService
{
    #[AsServiceMethod]
    #[Rest('/some-method/{id}', methods: Rest::GET)]
    public function someMethod(string $id): string
    {
        return 'Hello, World!';
    }
}