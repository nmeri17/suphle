<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Errors\ExceptionRenderer;

	abstract class ModuleAssembly {

		private $requestDetails;
		
		abstract protected function getModules():array;
		
		public function orchestrate():void {

			$this->bootInterceptor();

			echo $this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			new ExceptionRenderer($this->getErrorHandlers(), new Container);

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():string {

			if ($this->isLoginRequest())

				return $this->handleLoginRequest();

			$wrapper = $this->getFlowWrapper();

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest();
		}

		private function handleGenericRequest ():string {

			$initializer = (new ModuleToRoute)

			->findContext($this->getModules());

			if ($initializer)

				return $initializer->whenActive()

				->triggerRequest();

			throw new NotFoundException;
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

			return [
				NotFoundException::class => "handler",

				Unauthenticated::class => "handler",

				ValidationFailure::class => "handler",

				IncompatibleHttpMethod::class => "handler"
			];
		}

		private function handleLoginRequest ():string {

			// AuthContract
		}

		private function isLoginRequest ():bool {

			// needs requestDetails and AuthContract, implying the need for a container
		}
	}
?>