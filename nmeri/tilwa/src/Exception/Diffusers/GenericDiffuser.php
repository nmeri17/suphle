<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Tilwa\Request\RequestDetails;

	use Tilwa\Response\Format\{ Markup, Json};

	use Throwable;

	class GenericDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails,

		$origin, $controllerAction = "genericHandler";

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function setContextualData (Throwable $origin):void {

			$this->origin = $origin;
		}

		public function prepareRendererData ():void {

			if ($this->requestDetails->isApiRoute())

				$this->renderer = $this->getApiRenderer();

			else $this->renderer = $this->getMarkupRenderer();

			$this->renderer->setRawResponse([

				"message" => $this->origin->getMessage() ?? get_class($this->origin)
			]);

			$incomingCode = $this->origin->getCode();

			$this->renderer->setHeaders($incomingCode > 0 ? $incomingCode: 500, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():BaseRenderer {

			return new Json($this->controllerAction);
		}

		protected function getMarkupRenderer ():BaseRenderer {

			return new Markup($this->controllerAction, "/errors/default");
		}
	}
?>