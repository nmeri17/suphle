<?php
	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	class ModulesBooter {

		private $modules;

		public function __construct (array $modules) {

			$this->modules = $modules;
		}
		
		public function prepare():void {

			new EnvironmentDefaults;

			$this->injectConfigs();

			(new ModuleLevelEvents)->bootReactiveLogger($this->modules);
		}

		// We're setting these to be able to attach events soon after
		private function injectConfigs ():void {

			foreach ($this->modules as $descriptor)

				$descriptor->getContainer()->setConfigs($descriptor->getConfigs());
		}
	}
?>