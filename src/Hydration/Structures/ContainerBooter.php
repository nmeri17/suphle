<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Hydration\Container;

	class ContainerBooter {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function initializeContainer (string $interfaceList):void {

			$this->container->setEssentials();

			$this->container->setInterfaceHydrator($interfaceList);

			$this->container->interiorDecorate();
		}
	}
?>