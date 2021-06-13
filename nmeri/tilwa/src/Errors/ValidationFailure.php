<?php

	namespace Tilwa\Errors;

	class ValidationFailure { // these go to the handler. the error class name may only receive a container with which the actual handler will be hydrated

		public function __construct (RequestDetails $requestDetails, ControllerManager $controllerManager, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->controllerManager = $controllerManager;

			$this->router = $router;
		}

		public function getResponse () {

			if (!$this->requestDetails->isApiRoute()) {

				$this->controllerManager->revertRequest($this->router->getPreviousRequest());

				return $this->router->getPreviousRenderer();
			}
			// handle api validation errors
		}
	}
?>