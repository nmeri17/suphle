<?php
	namespace Tilwa\Hydration;

	use Tilwa\Contracts\{Config\ConfigMarker, Hydration\InterfaceCollection};

	use Tilwa\Modules\ModuleDescriptor;

	class InterfaceHydrator {

		private $container, $collection;

		public function __construct (InterfaceCollection $collection, Container $container) {

			$this->collection = $collection;

			$this->container = $container;
		}

		/**
		 * Loads them from sources in their order of importance i.e. configs are at the lowest level. We assume they don't inject additional dependencies
		*/
		public function deriveConcrete(string $interface) {

			$collection = $this->collection;

			$container = $this->container;

			if ($this->isConfig($interface))

				return $container->instantiateConcrete($collection->getConfigs()[$interface]); // we can't have custom config

			$simpleBinds = $collection->simpleBinds();

			if (array_key_exists($interface, $simpleBinds))

				return $container->instantiateConcrete( $simpleBinds[$interface]);

			$loaders = $collection->getLoaders();

			if (!array_key_exists($interface, $loaders)) {

				$loader = $container->instantiateConcrete($loaders[$interface]);

				return $this->extractFromLoader($loader);
			}

			$modules = $collection->getDelegatedInstances();

			if (!array_key_exists($interface, $modules))

				return $this->moduleDependencyConnector($interface, $modules[$interface]);
		}

		protected function isConfig(string $interface):bool {
			
			return in_array(ConfigMarker::class, class_implements($interface));
		}

		protected function extractFromLoader (BaseInterfaceLoader $loader) {

			$name = $loader->concrete();

			$this->container->whenType($name)

			->needsArguments($loader->bindArguments());
				
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