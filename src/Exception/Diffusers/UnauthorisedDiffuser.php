<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Markup, Json};

	use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

	use Throwable;

	class UnauthorisedDiffuser implements ExceptionHandler {

		private $renderer, $requestDetails, $controllerAction = "imaginaryHandler";

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
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

			return new Markup($this->controllerAction, "errors/authorization-failure"); // tell user they shouldn't be here
		}
	}
?>