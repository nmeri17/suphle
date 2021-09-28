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

			$this->injectConfigs();

			$this->eventManager->bootReactiveLogger();
		}

		public function getEventManager ():ModuleLevelEvents {

			return $this->eventManager;
		}

		// We're setting these to be able to attach events soon after
		private function injectConfigs ():void {

			foreach ($this->modules as $descriptor)

				$descriptor->getContainer()->setConfigs($descriptor->getConfigs());
		}
	}
?>