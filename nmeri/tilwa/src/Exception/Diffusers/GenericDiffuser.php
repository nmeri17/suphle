<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\Exception\ExceptionHandler;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Response\Format\{AbstractRenderer, Markup, Json};

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

			$this->renderer->setHeaders($this->origin->getCode() ?? 500, []);
		}

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():AbstractRenderer {

			return (new Json($this->controllerAction))

			->setRawResponse([

				"message" => $this->origin->getMessage() ?? get_class($this->origin)
			]);
		}

		protected function getMarkupRenderer ():AbstractRenderer {

			return new Markup($this->controllerAction, "errors/default");
		}
	}
?>