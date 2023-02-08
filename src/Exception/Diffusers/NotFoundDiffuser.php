<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\{ComponentEntry, Explosives\NotFoundException};

	use Throwable;

	class NotFoundDiffuser implements ExceptionHandler {

		protected BaseRenderer $renderer;
  
  		protected string $controllerAction = "missingHandler";

		public function __construct(protected readonly RequestDetails $requestDetails, protected readonly ComponentEntry $componentEntry) {

			//
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

			$url = $this->requestDetails->getPath();

			$this->renderer->setRawResponse([

				"url" => $url,

				"message" => $url . " Not Found"
			])->setHeaders(404, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():Json {

			return new Json($this->controllerAction);
		}

		protected function getMarkupRenderer ():BaseRenderer {

			return (new Markup($this->controllerAction, "not-found"))
			
			->setFilePath(
				$this->componentEntry->userLandMirror() . "Markup".

				DIRECTORY_SEPARATOR
			);
		}
	}
?>