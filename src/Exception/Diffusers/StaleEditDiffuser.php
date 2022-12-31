<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Presentation\BaseRenderer};

	use Suphle\Routing\RouteManager;

	use Suphle\Request\RequestDetails;

	use Suphle\Exception\Explosives\EditIntegrityException;

	use Throwable;

	class StaleEditDiffuser implements ExceptionHandler {

		private Throwable $renderer;

		public function __construct(protected readonly RequestDetails $requestDetails, protected readonly RouteManager $router) {

			//
		}

		/**
		 * @param {origin} EditIntegrityException
		*/
		public function setContextualData (Throwable $origin):void {

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

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}
	}
?>