<?php

namespace Suphle\Adapters\Orms\Eloquent;

use Suphle\Contracts\Database\{OrmTester, OrmDialect};

use PHPUnit\Framework\TestCase;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

/**
 * Extending TestCase to make [$this->assert...] available to the trait
*/
class DatabaseTester extends TestCase implements OrmTester
{
    use InteractsWithDatabase;

    private $connection;

    public function __construct(OrmDialect $ormDialect)
    {

        $this->connection = $ormDialect->getConnection();
    }

    public function __call(string $methodName, array $arguments)
    {

        return $this->$methodName(...$arguments); // instead of manually changing accessibility on underlying client
    }

    public function getConnection($connection = null)
    {

        return $this->connection;
    }
}
