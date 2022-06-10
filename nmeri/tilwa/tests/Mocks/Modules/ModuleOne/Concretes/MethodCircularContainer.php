<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Tilwa\Hydration\Container;

	class MethodCircularContainer {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function loadFromContainer ():MethodCircularConstructor {

			return $this->container->getClass(MethodCircularConstructor::class);
		}
	}
?>