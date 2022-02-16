<?php
	namespace Tilwa\Modules;

	use Tilwa\Events\ModuleLevelEvents;

	abstract class BaseExecutableModule {

		abstract public function entryModule ():ModuleDescriptor;

		public function boot ():void {

			$descriptor = $this->entryModule();

			$moduleList = [$descriptor];

			(new ModulesBooter($moduleList, new ModuleLevelEvents($moduleList)))
			->boot();

			$descriptor->warmUp();

			$descriptor->prepareToRun();
		}

		public function getContainer ():Container {

			return $this->entryModule()->getContainer();
		}
	}
?>