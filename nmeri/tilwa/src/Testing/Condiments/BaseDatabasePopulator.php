<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmTester};

	trait BaseDatabasePopulator {

		protected $replicator, $initialCount = 20,

		$databaseApi;

		protected static $isFirstTest = false,

		$staticReplicator; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

		protected function setUp ():void {

			parent::setUp();

			if (!static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

				$this->structureTable();

				static::$isFirstTest = true;
			}

			$this->replicator->seedDatabase( $this->initialCount); // maybe class is recreated for each test

			$this->replicator->listenForQueries();

			$this->databaseApi = $this->getContainer()->getClass(OrmTester::class);
		}

		private function structureTable ():void {

			static::$staticReplicator = $this->replicator = $replicator = $this->getContainer()->getClass(OrmReplicator::class);

			$replicator->setActiveModelType($this->getActiveEntity());

			$replicator->setupSchema();
		}

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