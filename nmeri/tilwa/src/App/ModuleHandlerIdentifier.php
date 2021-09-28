<?php

	namespace Tilwa\App;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Errors\ExceptionRenderer;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\{Config\Auth as AuthConfig, Auth\LoginRenderers};

	use Tilwa\Auth\LoginRequestHandler;

	abstract class ModuleHandlerIdentifier {

		private $requestDetails, $container, $authConfig;
		
		abstract protected function getModules():array;
		
		public function orchestrate():void {

			$modules = $this->getModules();

			$this->setContainer($modules);

			$this->extractFromContainer();

			(new ModulesBooter($modules))->boot();

			new ExceptionRenderer($this->getErrorHandlers(), $this->container);

			echo $this->beginRequest();
		}
		
		private function beginRequest():string {

			$modules = $this->getModules();

			if ($this->requestDetails->isPostRequest() && $rendererName = $this->getLoginRenderer())

				return $this->handleLoginRequest($rendererName);

			$wrapper = $this->getFlowWrapper($modules);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest($modules);
		}

		private function handleGenericRequest (array $modules):string {

			$initializer = (new ModuleToRoute)->findContext($modules);

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

		private function getFlowWrapper (array $modules):OuterFlowWrapper {

			$wrapperName = OuterFlowWrapper::class;

			return $this->container->whenType($wrapperName)

			->needsArguments([

				"modules" => $modules
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

		private function setContainer (array $modules):void {

			$this->container = current($modules)->getContainer();
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
	}
?>