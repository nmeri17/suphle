<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Hydration\Container;

	class ContainerBooter {

		public function __construct(private readonly Container $container)
  {
  }

		public function initializeContainer (string $interfaceList):void {

			$this->container->setEssentials();

			$this->container->setInterfaceHydrator($interfaceList);

			$this->container->interiorDecorate();
		}
	}
?>