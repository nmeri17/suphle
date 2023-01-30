<?php
	namespace Suphle\Testing\Condiments;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	trait ModuleReplicator {

		/**
		 * Is only usable on test types extending TestVirginContainer
		*/
		protected function replicateModule (
			string $descriptor, callable $customizer,

			bool $stubsDecorator = false, array $descriptorStubs = []
		):ModuleDescriptor {

			if ($stubsDecorator)

				$container = $this->positiveDouble(Container::class, [

					"getDecorator" => $this->stubDecorator()
				]);

			else $container = new Container;

			$this->bootContainer($container);

			$writer = new WriteOnlyContainer($container); // using unique instances rather than a fixed one so test can make multiple calls to clone modules

			$customizer($writer);

			return $this->replaceConstructorArguments(

				$descriptor, compact("container"), $descriptorStubs
			);
		}
	}
?>