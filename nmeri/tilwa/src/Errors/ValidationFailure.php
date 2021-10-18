<?php
	namespace Tilwa\Errors;

	class ValidationFailure { // these go to the handler. the error class name may only receive a container with which the actual handler will be hydrated
		// Note: this should be able to receive loginRequestHandlers too, not just controllerManagers

		public function __construct (RequestDetails $requestDetails, ControllerManager $controllerManager, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->controllerManager = $controllerManager;

			$this->router = $router;
		}

		// should return psr\responseInterface instead
		public function getResponse ():AbstractRenderer {

			$router = $this->router;

			if (!$this->requestDetails->isApiRoute())

				return $router->getPreviousRenderer();

			$renderer = $router->getActiveRenderer();

			$renderer->setRawResponse($this->controllerManager->getValidatorErrors());

			return $renderer;
		}
	}
?>