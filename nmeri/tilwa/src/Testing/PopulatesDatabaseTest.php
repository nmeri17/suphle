<?php
	namespace Tilwa\Testing;

	use Tilwa\Contracts\Database\Orm;

	abstract class PopulatesDatabaseTest extends BaseTest {

		private $orm, $entityInstance;

		protected $initialCount = 20;

		abstract protected function getActiveEntity ():string;

		protected function setUp ():void {

			parent::setUp();

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

		// todo: truncate table in the tearDown
	}
?>