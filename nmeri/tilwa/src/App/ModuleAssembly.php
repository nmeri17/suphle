<?php

	namespace Tilwa\App;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Errors\ExceptionRenderer;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\{Config\Auth as AuthConfig, LoginRenderers};

	use Tilwa\Auth\LoginRequestHandler;

	abstract class ModuleAssembly {

		private $requestDetails, $container, $authConfig;
		
		abstract protected function getModules():array;
		
		public function orchestrate():void {

			$this->setContainer();

			$this->extractFromContainer();

			$this->bootInterceptor();

			echo $this->beginRequest();
		}
		
		private function bootInterceptor():void {

			new EnvironmentDefaults;

			new ExceptionRenderer($this->getErrorHandlers(), $this->container);

			$this->injectConfigs();

			(new ModuleLevelEvents)->bootReactiveLogger($this->getModules());
		}
		
		private function beginRequest():string {

			if ($this->requestDetails->isPostRequest() && $rendererName = $this->getLoginRenderer())

				return $this->handleLoginRequest($rendererName);

			$wrapper = $this->getFlowWrapper();

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest();
		}

		private function handleGenericRequest ():string {

			$initializer = (new ModuleToRoute)->findContext($this->getModules());

			if ($initializer)

				return $initializer->whenActive()->triggerRequest();

			throw new NotFoundException;
		}

		private function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function getFlowWrapper ():OuterFlowWrapper {

			$wrapperName = OuterFlowWrapper::class;

			return $this->container->whenType($wrapperName)

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

		private function setContainer ():void {

			$this->container = current($this->getModules())->getContainer();
		}

		private function extractFromContainer ():void {

			$this->requestDetails = $this->container->getClass(RequestDetails::class);

			$this->authConfig = $this->container->getClass(AuthConfig::class);
		}

		/**
		 * @return A Tilwa\Contracts\LoginRenderers::class when the incoming path matches one of the login paths configured
		*/
		private function getLoginRenderer ():?string {

			return $this->authConfig->getPathRenderer($this->requestDetails->getPath());
		}

		private function handleLoginRequest (string $rendererName):string {

			$renderer = $this->container->getClass($rendererName);

			return (new LoginRequestHandler($renderer, $this->container))->getResponse();
		}

		// We're setting these to be able to attach events soon after
		private function injectConfigs ():void {

			foreach ($this->getModules() as $descriptor)

				$descriptor->getContainer()->setConfigs($descriptor->getConfigs());
		}
	}
?>