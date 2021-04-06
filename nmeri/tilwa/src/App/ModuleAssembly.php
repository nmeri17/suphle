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

			$requestPath = $_GET['tilwa_request'];

			if ($flow->shouldRespond()) {

				$response = $flow->getResponse($requestPath); // i need the user id here
					
				$flow->afterRender();

				return $response;
			}
			return (new ModuleToRoute)

			->findContext($this->getModules(), $requestPath)

			->trigger();
		}
	}
?>