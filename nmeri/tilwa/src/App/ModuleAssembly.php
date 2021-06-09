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

			$wrapperName = OuterFlowWrapper::class;

			$wrapper = $this->setContainer()

			->provisionWrapper($requestPath, $wrapperName)

			->getClass($wrapperName);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			$initializer = (new ModuleToRoute)

			->findContext($this->getModules());

			if ($initializer)

				return $initializer->whenActive()

				->triggerRequest();

			// throw a 404 error to be caught by the exception renderer
		}

		private function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function setContainer():self {

			$randomModule = current($this->getModules());

			$this->container = $randomModule->getContainer();

			return $this;
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