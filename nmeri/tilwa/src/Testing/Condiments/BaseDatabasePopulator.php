<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmTester};

	trait BaseDatabasePopulator {

		protected $replicator, $initialCount = 15,

		$databaseApi;

		protected static $isFirstTest = false,

		$staticReplicator; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

		protected function setUp ():void {

			parent::setUp();

			if (!static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

				$this->structureTable();

				static::$isFirstTest = true;
			}

			else $this->replicator = static::$staticReplicator;

			$this->replicator->listenForQueries(); // note: since we have no control/wrapper around actual running test, if it throws \Error, the seeding below won't be rolled back

			$this->replicator->seedDatabase( $this->initialCount);

			$this->databaseApi = $this->getContainer()->getClass(OrmTester::class);
		}

		private function structureTable ():void {

			static::$staticReplicator = $this->replicator = $replicator = $this->getContainer()->getClass(OrmReplicator::class);

			$replicator->setActiveModelType($this->getActiveEntity());

			$replicator->setupSchema();
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