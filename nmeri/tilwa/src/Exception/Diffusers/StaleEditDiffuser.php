<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\Exception\ExceptionHandler;

	use Tilwa\Routing\{RouteManager, RequestDetails};

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	class StaleEditDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $router;

		public function __construct (RequestDetails $requestDetails, RouteManager $router) {

			$this->requestDetails = $requestDetails;

			$this->router = $router;
		}

		public function setContextualData (EditIntegrityException $origin):void {

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

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}
	}
?>