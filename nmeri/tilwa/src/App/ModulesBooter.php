<?php
	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	class ModulesBooter {

		private $modules, $eventManager;

		public function __construct (array $modules) {

			$this->modules = $modules;

			$this->eventManager = new ModuleLevelEvents($modules);
		}
		
		public function boot():void {

			new EnvironmentDefaults;

			foreach ($this->modules as $descriptor)

				$descriptor->absorbConfigs(); // We're setting these to be able to attach events soon after

			$this->eventManager->bootReactiveLogger();
		}

		public function getEventManager ():ModuleLevelEvents {

			return $this->eventManager;
		}
	}
?>