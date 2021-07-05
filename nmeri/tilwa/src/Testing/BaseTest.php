<?php

	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel};

	use Tilwa\Config\{ Services, Laravel};

	use PHPUnit\Framework\TestCase;

	use Prophecy\Prophet;

	class BaseTest extends TestCase {

		protected $prophet, $container;

		protected function setUp ():void {

			$this->prophet = new Prophet;

			$this->container = new Container; // for internal module testing, override this. Cycle through ModuleAssembly looking for which one prefixes our location

			$this->bootContainer();
		}

		protected function tearDown ():void {

			$this->prophet->checkPredictions();
		}

		private function bootContainer ():void {

			$this->container->setConfigs($this->containerConfigs());
		}

		protected function containerConfigs ():array {

			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class
			];
		}
	}
?>