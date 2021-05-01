<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\OuterFlowWrapper;

	abstract class ModuleAssembly {

		private $container;
		
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

			$requestPath = $_GET['tilwa_path'];

			$wrapperName = OuterFlowWrapper::class;

			$wrapper = $this->setContainer()

			->provisionWrapper($requestPath, $wrapperName)

			->getClass($wrapperName);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return (new ModuleToRoute)

			->findContext($this->getModules(), $requestPath)

			->trigger();
		}

		private function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function setContainer():Container {

			$randomModule = current($this->getModules());

			return $this->container = $randomModule->getContainer();
		}

		private function provisionWrapper(string $requestPath, string $wrapperName):Container {

			return $this->container->whenType($wrapperName)

			->needsArguments([
				"pattern" => $requestPath,

				"modules" => $this->getModules()
			]);
		}
	}
?>