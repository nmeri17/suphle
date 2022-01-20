<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\Exception\ExceptionHandler;

	use Tilwa\Routing\{RouteManager, RequestDetails};

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Exception\Explosives\ValidationFailure;

	class ValidationFailureDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $router, $validationEvaluator;

		public function __construct (RequestDetails $requestDetails, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->router = $router;
		}

		public function setContextualData (ValidationFailure $origin):void {

			$this->validationEvaluator = $origin->getEvaluator();
		}

		public function prepareRendererData ():void {

			if (!$this->requestDetails->isApiRoute())

				$this->renderer = $this->router->getPreviousRenderer();

			else $this->renderer = $this->router->getActiveRenderer();

			$this->renderer->setRawResponse($this->validationEvaluator->getValidatorErrors())

			->setHeaders(422, []);
		}

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}
	}
?>