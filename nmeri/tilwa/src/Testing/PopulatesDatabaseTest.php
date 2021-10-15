<?php
	namespace Tilwa\Testing;

	use Tilwa\Contracts\Database\Orm;

	trait PopulatesDatabaseTest {

		private $orm, $entityInstance;

		protected $container, $initialCount = 20;

		abstract protected function getActiveEntity ():string;

		protected function setUp ():void {

			parent::setUp(); // calls the one on the inherited class of the test this is applied to

			$this->entityInstance = $this->container->getClass($this->getActiveEntity()); // may need further customization since these are special kind of objects

			$this->orm = $this->container->getClass(Orm::class); // check what arguments are on the eloquent's constructor and inject those into our container

			$this->orm->factoryProduce($this->entityInstance, $this->initialCount);
		}

		protected function getBeforeInsertion (int $amount = 1, array $overrides = []) {

			return $this->orm->factoryLine($this->entityInstance, $amount, $overrides);
		}

		protected function getRandomEntity () {

			return $this->orm->findAny($this->entityInstance);
		}

		protected function getRandomEntities (int $amount):array {

			return $this->orm->findAnyMany($this->entityInstance, $amount);
		}

		// todo: truncate table in the tearDown
	}
?>