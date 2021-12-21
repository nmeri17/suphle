<?php
	namespace Tilwa\Modules;

	use Tilwa\Events\ModuleLevelEvents;

	class ModulesBooter {

		private $modules, $eventManager;

		public function __construct (array $modules, ModuleLevelEvents $eventManager) {

			$this->modules = $modules;

			$this->eventManager = $eventManager;
		}
		
		public function boot():void {

			new EnvironmentDefaults;

			foreach ($this->modules as $descriptor)

				$descriptor->warmUp(); // We're setting these to be able to attach events soon after

			$this->eventManager->bootReactiveLogger();
		}
	}
?>