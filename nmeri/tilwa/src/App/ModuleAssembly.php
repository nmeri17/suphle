<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\FlowFinder;

	abstract class ModuleAssembly {
		
		abstract public function getModules():array;
		
		public function orchestrate():void {

			$this->bootInterceptor();

			echo $this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():string {

			$flow = new FlowFinder;

			if ($flow->shouldRespond()) {

				$response = $flow->getResponse(); // should set [renderer] for other methods to use
					
				$flow->flush();

				return $response;
			}
			return (new ModuleToRoute)->findContext($this->getModules())->trigger();
		}
	}
?>