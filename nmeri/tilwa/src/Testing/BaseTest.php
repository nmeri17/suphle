<?php

	namespace Tilwa\Testing;

	use Tilwa\App\{Container, ModuleDescriptor};

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

			$configs = $this->containerConfigs(); $h = $this->container->getClass(ModuleDescriptor::class);
var_dump($configs, $h);die();
			if (empty($configs))

				$configs = $h->getConfigs();

			$this->container->setConfigs($configs);
		}

		protected function containerConfigs ():array {

			return [];
		}
	}
?>