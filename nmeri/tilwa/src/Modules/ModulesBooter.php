<?php
	namespace Tilwa\Modules;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Contracts\Config\ModuleFiles;

	use Dotenv\Dotenv;

	class ModulesBooter {

		private $modules, $eventManager;

		public function __construct (array $modules, ModuleLevelEvents $eventManager) {

			$this->modules = $modules;

			$this->eventManager = $eventManager;
		}
		
		public function boot():void {

			foreach ($this->modules as $descriptor) {

				$this->loadEnv($descriptor->fileConfig());

				$descriptor->warmUp(); // We're setting these to be able to attach events soon after

				$descriptor->getContainer()->whenTypeAny()->needsAny([

					ModuleFiles::class => $descriptor->fileConfig()
				]);
			}

			$this->eventManager->bootReactiveLogger();
		}

		protected function loadEnv (ModuleFiles $fileConfig):void {		

			Dotenv::createImmutable( $fileConfig->activeModulePath())

			->load();
		}
	}
?>