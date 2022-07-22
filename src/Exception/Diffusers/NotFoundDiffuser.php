<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\Explosives\NotFoundException;

	use Throwable;

	class NotFoundDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails,

		$controllerAction = "missingHandler";

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		/**
		 * @param {origin} NotFoundException
		*/
		public function setContextualData (Throwable $origin):void {

			//
		}

		public function prepareRendererData ():void {

			if ($this->requestDetails->isApiRoute())

				$this->renderer = $this->getApiRenderer();

			else $this->renderer = $this->getMarkupRenderer();

			$this->renderer->setHeaders(404, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():Json {

			return (new Json($this->controllerAction))

			->setRawResponse([ "message" => "Not Found" ]);
		}

		protected function getMarkupRenderer ():Markup {

			return (new Markup($this->controllerAction, "errors/not-found"))
			->setRawResponse([

				"url" => $this->requestDetails->getPath()
			]);
		}
	}
?>