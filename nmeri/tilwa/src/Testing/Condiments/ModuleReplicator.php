<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	trait ModuleReplicator {

		/**
		 * Is only usable on test types extending TestVirginContainer
		*/
		protected function replicateModule(string $descriptor, callable $customizer, bool $stubsDecorator = true):ModuleDescriptor {

			if ($stubsDecorator)

				$container = $this->positiveDouble(Container::class, [

					"getDecorator" => $this->stubDecorator()
				]);

			else $container = new Container;

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$writer = new WriteOnlyContainer($container); // using unique instances rather than a fixed one so test can make multiple calls to clone modules

			$customizer($writer);

			return new $descriptor($container);
		}
	}
?>