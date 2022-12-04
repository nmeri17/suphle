<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\{ComponentEntry, Explosives\UnauthorizedServiceAccess};

	use Throwable;

	class UnauthorizedDiffuser implements ExceptionHandler {

		private $renderer;
  private string $controllerAction = "imaginaryHandler";

		public function __construct(private readonly RequestDetails $requestDetails, private readonly ComponentEntry $componentEntry)
  {
  }

		/**
		 * @param {origin} UnauthorizedServiceAccess
		*/
		public function setContextualData (Throwable $origin):void {

			//
		}

		public function prepareRendererData ():void {

			if ($this->requestDetails->isApiRoute())

				$this->renderer = $this->getApiRenderer();

			else $this->renderer = $this->getMarkupRenderer();

			$this->renderer->setHeaders(403, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getApiRenderer ():BaseRenderer {

			return (new Json($this->controllerAction))

			->setRawResponse([ "message" => "Unauthorized" ]);
		}

		protected function getMarkupRenderer ():Markup {

			$path = $this->componentEntry->userLandMirror();

			return (new Markup($this->controllerAction, "authorization-failure"))
			
			->setFilePaths(
				$path . "Markup". DIRECTORY_SEPARATOR,

				$path . "Tss". DIRECTORY_SEPARATOR
			);
		}
	}
?>