<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\Exception\ExceptionHandler;

	use Tilwa\Routing\{RouteManager, RequestDetails};

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Exception\Explosives\ValidationFailure;

	use Throwable;

	class ValidationFailureDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $router, $validationEvaluator;

		public function __construct (RequestDetails $requestDetails, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->router = $router;
		}

		/**
		 * @param {origin} ValidationFailure
		*/
		public function setContextualData (Throwable $origin):void {

			$this->validationEvaluator = $origin->getEvaluator();
		}

		public function prepareRendererData ():void {

			if (!$this->requestDetails->isApiRoute())

				$this->renderer = $this->router->getPreviousRenderer();

			else $this->renderer = $this->router->getActiveRenderer();

			$this->renderer->setRawResponse(array_merge($this->renderer->getRawResponse(), [ // this means every route that can possibly fail validation should return an array

				"errors" => $this->validationErrors()
			]))
			->setHeaders(422, []);
		}

		protected function validationErrors ():array {

			return $this->validationEvaluator->getValidatorErrors();
		}

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}
	}
?>