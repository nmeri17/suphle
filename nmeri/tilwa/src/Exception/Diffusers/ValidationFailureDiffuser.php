<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Request\RequestDetails;

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

			$this->renderer->setRawResponse(array_merge(

				$this->getArrayResponse(), [

				"errors" => $this->validationErrors()
			]))
			->setHeaders(422, []);
		}

		/**
		* Insurance against routes that can possibly fail validation that don't return an array
		*/
		protected function getArrayResponse ():array {

			$responseBody = $this->renderer->getRawResponse();

			if (is_array($responseBody)) return $responseBody;

			if (is_iterable($responseBody))

				return json_decode(json_encode($responseBody), true);

			return [$responseBody];
		}

		protected function validationErrors ():array {

			return $this->validationEvaluator->getValidatorErrors();
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}
	}
?>