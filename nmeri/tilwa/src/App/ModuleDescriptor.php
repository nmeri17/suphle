<?php

	namespace Tilwa\App;

	use Tilwa\Contracts\Config\{Authentication, Events as EventConfig, ModuleFiles, Router, Services, Executables, LaravelApp};

	abstract class ModuleDescriptor {

		protected $container;

		private $dependsOn;

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		public function setDependsOn(array $dependencies):self {
			
			foreach ($dependencies as $contract => $concrete) {

				$service = $concrete->exports();
				
				if ($service instanceof $contract) {

					$pair = [$contract => $service];

					$this->dependsOn += $pair;

					$this->container->whenTypeAny()->needsAny($pair);
				}
			}
		}

		// @return interfaces[] from `Interactions` namespace
		public function getDependsOn():array {

			return $this->dependsOn;
		}

		// @return concrete implementing `exportsImplements`
		abstract public function exports():object;

		// interface from Interactions namespace which will be consumers API with this module
		abstract public function exportsImplements():string;

		// arguments will be auto-wired
		public function entityBindings ():self {

			return $this;
		}

		public function getContainer():Container {
			
			return $this->container;
		}

		public function getLibraryConfigurations():array {
			
			return []; // populate this with tilwa\contracts\config => eachConfig
		}

		// this is living here instead of on moduleLevelEvents because he's the one who knows what config is applicable to this module
		public function getEventManager():EventManager {

			$container = $this->container;

			$eventConfig = $container->getClass(EventConfig::class); // we might get stomped here cuz by the time this is running, module hasn't been booted yet

			if ($eventConfig)

				return $container->getClass($eventConfig->getManager());
		}
	}
?>