<?php
	namespace Suphle\Hydration;

	use Suphle\Contracts\{Config\ConfigMarker, Hydration\InterfaceCollection};

	use Suphle\Modules\ModuleDescriptor;

	class InterfaceHydrator {

		public function __construct(protected readonly InterfaceCollection $collection, protected readonly Container $container) {

			//
		}

		/**
		 * Loads them from sources in their order of importance i.e. configs are at the lowest level. We assume they don't inject additional dependencies
		*/
		public function deriveConcrete(string $interface) {

			$collection = $this->collection;

			$container = $this->container;

			if ($this->isConfig($interface)) {

				$configs = $collection->getConfigs();

				if (!array_key_exists($interface, $configs)) return;

				return $container->instantiateConcrete($configs[$interface]); // not using `getClass` since we can't have custom config
			}

			$simpleBinds = $collection->simpleBinds();

			if (array_key_exists($interface, $simpleBinds))

				return $container->instantiateConcrete( $simpleBinds[$interface]);

			$loaders = $collection->getLoaders();

			if (array_key_exists($interface, $loaders)) {

				$loader = $container->getClass($loaders[$interface]);

				return $this->extractFromLoader($loader);
			}

			$modules = $collection->getDelegatedInstances();

			if (array_key_exists($interface, $modules))

				return $this->moduleDependencyConnector($interface, $modules[$interface]);
		}

		protected function isConfig(string $interface):bool {
			
			return in_array(ConfigMarker::class, class_implements($interface));
		}

		protected function extractFromLoader (BaseInterfaceLoader $loader) {

			$name = $loader->concreteName();

			$concreteArguments = $loader->bindArguments(); // call separately so it doesn't mess with the provision below

			$this->container->whenType($name)

			->needsArguments($concreteArguments);

			$concrete = $this->container->instantiateConcrete($name);

			$loader->afterBind($concrete);

			return $concrete;
		}

		// ask their container to hydrate it on our behalf
		protected function moduleDependencyConnector(string $contract, ModuleDescriptor $descriptor) {

			return $descriptor->materialize();
		}
	}
?>