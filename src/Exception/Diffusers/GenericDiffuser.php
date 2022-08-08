<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

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

				"exception" => $this->origin
			]);

			$incomingCode = $this->origin->getCode();

			$this->renderer->setHeaders($incomingCode > 100 ? $incomingCode: 500, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():BaseRenderer {

			return new Json($this->controllerAction);
		}

		protected function getMarkupRenderer ():BaseRenderer {

			return new Markup($this->controllerAction, "errors/default");
		}
	}
?>