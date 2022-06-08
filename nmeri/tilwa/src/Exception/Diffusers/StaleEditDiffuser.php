<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use Throwable;

	class StaleEditDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $router;

		public function __construct (RequestDetails $requestDetails, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->router = $router;
		}

		/**
		 * @param {origin} EditIntegrityException
		*/
		public function setContextualData (Throwable $origin):void {

			//
		}

		public function prepareRendererData ():void {

			if (!$this->requestDetails->isApiRoute())

				$this->renderer = $this->router->getPreviousRenderer();

			else $this->renderer = $this->router->getActiveRenderer();

			$this->renderer->setRawResponse(array_merge($this->renderer->getRawResponse(), [

				"errors" => [["message" => "Another user recently updated this resource"]]
			]))
			->setHeaders(400, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}
	}
?>