<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Database\OrmReplicator;

	trait PopulatesDatabaseTest {

		protected $replicator, $initialCount = 20;

		abstract protected function getActiveEntity ():string;

		public static function setUpBeforeClass ():void {

			parent::setUpBeforeClass();

			$replicator = $this->container->getClass(OrmReplicator::class);

			$replicator->setActiveModelType($this->getActiveEntity());

			$replicator->setupSchema();

			$this->replicator = $replicator;
		}

		public static function tearDownAfterClass ():void {

			$this->replicator->dismantleSchema();

			parent::tearDownAfterClass();
		}

		protected function setUp ():void {

			parent::setUp();

			$this->replicator->seedDatabase( $this->initialCount);

			$this->replicator->listenForQueries();
		}

		protected function tearDown ():void {

			$this->replicator->stopQueryListen();

			parent::tearDown();
		}
	}
?>