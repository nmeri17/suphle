<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, Extensions\CheckProvisionedClasses};

	trait ModuleReplicator {

		/**
		 * Is only usable on test types extending TestVirginContainer
		*/
		protected function replicateModule(string $descriptor, callable $customizer, bool $stubsDecorator = true):ModuleDescriptor {

			if ($stubsDecorator)

				$container = $this->positiveDouble(CheckProvisionedClasses::class, [

					"getDecorator" => $this->stubDecorator()
				]);

			else $container = new CheckProvisionedClasses;

			$this->bootContainer($container);

			$writer = new WriteOnlyContainer($container); // using unique instances rather than a fixed one so test can make multiple calls to clone modules

			$customizer($writer);

			return new $descriptor($container);
		}
	}
?>