<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\OrmReplicator;

	trait BaseDatabasePopulator {

		protected $replicator, $initialCount = 20;

		protected static $isFirstTest = false,

		$staticReplicator; // duplicating this value since it's the one used in [tearDownAfterClass] but we want to maintain consistency using `$this` instead of a static property

		abstract protected function getActiveEntity ():string;

		private function structureTable ($container):void {

			static::$staticReplicator = $this->replicator = $replicator = $container->getClass(OrmReplicator::class);

			$replicator->setActiveModelType($this->getActiveEntity());

			$replicator->setupSchema();
		}

		public static function tearDownAfterClass ():void {

			static::$staticReplicator->dismantleSchema();

			parent::tearDownAfterClass();
		}

		protected function setUp ():void {

			parent::setUp();

			if (!static::$isFirstTest) { // testBeforeClass is the designated method for universal setups like this. But container needed for extracting replicator is unavailable then

				$this->structureTable($this->getContainer());

				static::$isFirstTest = true;
			}

			$this->replicator->seedDatabase( $this->initialCount);

			$this->replicator->listenForQueries();
		}

		protected function tearDown ():void {

			$this->replicator->stopQueryListen();

			parent::tearDown();
		}
	}
?>