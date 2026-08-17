<?php
namespace _modules_shell\_module_name\Tests\Auth;

use Suphle\Hydration\Container;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use _modules_shell\_module_name\Meta\_module_nameDescriptor;
use _database_namespace_\User;

class BaseAuthTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;

    protected function getModules(): array
    {
        return [new _module_nameDescriptor(new Container)];
    }

    protected function getActiveEntity(): string
    {
        return User::class;
    }
}