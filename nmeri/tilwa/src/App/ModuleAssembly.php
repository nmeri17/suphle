<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\FlowFinder;

	abstract class ModuleAssembly {
		
		abstract public function getModules():array;
		
		public function orchestrate():void {

			$this->bootInterceptor();

			$this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():void {

			$flow = new FlowFinder;

			if ($flow->shouldRespond()) {

				echo $flow->getResponse(); // should set [renderer] for other methods to use
					
				$flow->flush();
			}
			else echo (new ModuleToRoute)->findContext($this->getModules())->trigger();
		}
	}
?>