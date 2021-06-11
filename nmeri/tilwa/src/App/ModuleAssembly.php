<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Errors\ExceptionRenderer;

	abstract class ModuleAssembly {

		private $container;
		
		abstract protected function getModules():array;
		
		public function orchestrate():void {

			$this->bootInterceptor();

			echo $this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():string {

			$wrapper = $this->getFlowWrapper();

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			$initializer = (new ModuleToRoute)

			->findContext($this->getModules()); // wrap in try/catch and throw if http method doesn't match

			if ($initializer)

				return $initializer->whenActive()

				->triggerRequest();

			return (new ExceptionRenderer($this->getErrorHandlers))
			->throw( 404);
		}

		private function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function getFlowWrapper ():OuterFlowWrapper {

			$wrapperName = OuterFlowWrapper::class

			return current($this->getModules())

			->getContainer()->whenType($wrapperName)

			->needsArguments([

				"modules" => $this->getModules()
			])

			->getClass($wrapperName);
		}

		protected function getErrorHandlers ():array {

			// [code => handler]
			return []; // dev can replace the handler with their instance
			// these are global handlers taken care of by the errorManager
		}
	}
?>