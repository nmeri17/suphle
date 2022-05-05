<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmTester, OrmDialect};

	trait BaseDatabasePopulator {

		protected $replicator, $databaseApi;

		protected static $isFirstTest = true, // using this to maintain state since PHPUnit resets all instance properties per test

		$staticReplicator; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

		protected function setUp ():void {

			parent::setUp();

			$this->setReplicator();

			$this->databaseApi = $this->getContainer()->getClass(OrmTester::class);

			if (static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

				$this->replicator->setupSchema();

				$this->replicator->seedDatabase($this->getInitialCount());

				static::$isFirstTest = false;
			}

			$this->replicator->listenForQueries();
		}

		protected function getInitialCount ():int {

			return 10;
		}

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

		public static function tearDownAfterClass ():void {

			static::$staticReplicator->dismantleSchema();

			parent::tearDownAfterClass();
		}
	}
?>