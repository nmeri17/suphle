<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\{ComponentEntry, Explosives\NotFoundException};

	use Throwable;

	class NotFoundDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $componentEntry,

		$controllerAction = "missingHandler";

		public function __construct (RequestDetails $requestDetails, ComponentEntry $componentEntry) {

			$this->requestDetails = $requestDetails;

			$this->componentEntry = $componentEntry;
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

			->setRawResponse([

				"message" => $this->requestDetails->getPath() . " Not Found"
			]);
		}

		protected function getMarkupRenderer ():Markup {

			$path = $this->componentEntry->userLandMirror();

			return (new Markup($this->controllerAction, "not-found"))
			
			->setFilePaths($path . "Markup", $path . "Tss")
			
			->setRawResponse([

				"url" => $this->requestDetails->getPath()
			]);
		}
	}
?>