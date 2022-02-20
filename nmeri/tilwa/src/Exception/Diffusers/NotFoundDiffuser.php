<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\Exception\ExceptionHandler;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Response\Format\{AbstractRenderer, Markup, Json};

	use Tilwa\Exception\Explosives\NotFoundException;

	class NotFoundDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails,

		$controllerAction = "missingHandler";

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function setContextualData (NotFoundException $origin):void {

			//
		}

		public function prepareRendererData ():void {

			if ($this->requestDetails->isApiRoute())

				$this->renderer = $this->getApiRenderer();

			else $this->renderer = $this->getMarkupRenderer();

			$this->renderer->setHeaders(404, []);
		}

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():AbstractRenderer {

			return (new Json($this->controllerAction))

			->setRawResponse([ "message" => "Not Found" ]);
		}

		protected function getMarkupRenderer ():AbstractRenderer {

			return new Markup($this->controllerAction, "errors/not-found");
		}
	}
?>