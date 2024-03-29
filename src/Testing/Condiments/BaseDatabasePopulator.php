<?php

namespace Suphle\Testing\Condiments;

use Suphle\Contracts\Database\{OrmReplicator, OrmTester, OrmDialect};

trait BaseDatabasePopulator
{
    protected OrmReplicator $replicator;

    protected OrmTester $databaseApi;

    protected static bool $isFirstTest = true; // using this to maintain state since PHPUnit resets all instance properties per test
    protected static ?OrmReplicator $staticReplicator = null; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

    protected function setUp(): void
    {

        parent::setUp();

        $this->databaseSetup();
    }

    /**
     * Use when some objects database setup depends on should be bound
    */
    protected function databaseSetup(): void
    {

        $this->setReplicator();

        $this->databaseApi = $this->getContainer()->getClass(OrmTester::class);

        /**
         I'm commenting below check out since PHPUnit doesn't reset static properties after each test. Which means every 2nd test using this trait will see `$isFirstTest` as true
         *
         * @backupStaticAttributes enabled doesn't have any effect
        */

        // if (static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

        $this->replicator->setupSchema();

        /*static::$isFirstTest = false;
            }*/

        $this->replicator->listenForQueries();

        $this->replicator->seedDatabase($this->getInitialCount());
    }

    protected function getInitialCount(): int // Using a method for this since consumers can't overwrite trait properties without the poor DX of renaming
    {return 10;
    }

    private function setReplicator(): void
    {

        $this->replicator = static::$staticReplicator = $this->getContainer()->getClass(OrmReplicator::class);

        $this->replicator->setActiveModelType($this->getActiveEntity());
    }

    /**
     * Does not take interfaces
    */
    abstract protected function getActiveEntity(): string;

    protected function tearDown(): void
    {

        $this->replicator->revertHeardQueries();

        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException if database connection was reset (e.g a refreshClass that wiped OrmDialect) without being restored in the current test, since the facades in the migrator will then try to use their factory to create an instance
    */
    public static function tearDownAfterClass(): void
    {

        if (!is_null(static::$staticReplicator)) { // will be null if an exception was thrown before or during setup

            static::$staticReplicator->dismantleSchema();
        }

        parent::tearDownAfterClass();
    }
}
