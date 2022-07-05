<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmTester, OrmDialect};

	trait BaseDatabasePopulator {

		protected $replicator, $databaseApi;

		protected static $isFirstTest = true, // using this to maintain state since PHPUnit resets all instance properties per test

		$staticReplicator; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

		protected function setUp ():void {

			parent::setUp();

			$this->databaseSetup();
		}

		/**
		 * Use when some objects database setup depends on should be bound
		*/
		protected function databaseSetup ():void {

			$this->setReplicator();

			$this->databaseApi = $this->getContainer()->getClass(OrmTester::class);

			/**
			 I'm commenting below check out since PHPUnit doesn't reset static properties after each test. Which means every 2nd test using this trait will see `$isFirstTest` as true
			 *
			 * @backupStaticAttributes enabled doesn't have any effect
			*/

			// if (static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

				$this->replicator->setupSchema();

				$this->replicator->seedDatabase($this->getInitialCount());

				/*static::$isFirstTest = false;
			}*/

			$this->preDatabaseFreeze();

			$this->replicator->listenForQueries();
		}

		protected function getInitialCount ():int {

			return 10;
		}

		/**
		 * Any row inserts expected to exist after a connection reset (i.e. changing url and refreshing OrmDialect), should occur here, not inside the test
		*/
		protected function preDatabaseFreeze ():void {}

		private function setReplicator ():void {

			$this->replicator = static::$staticReplicator = $this->getContainer()->getClass(OrmReplicator::class);

			$this->replicator->setActiveModelType($this->getActiveEntity());
		}

		/**
		 * Does not take interfaces
		*/
		abstract protected function getActiveEntity ():string;

		protected function tearDown ():void {

			$this->replicator->stopQueryListen();

			parent::tearDown();
		}

		/**
		 * @throws InvalidArgumentException if database connection was reset (e.g a refreshClass that wiped OrmDialect) without being restored in the current test, since the facades in the migrator will then try to use their factory to create an instance
		*/
		public static function tearDownAfterClass ():void {

			static::$staticReplicator->dismantleSchema();

			parent::tearDownAfterClass();
		}
	}
?>