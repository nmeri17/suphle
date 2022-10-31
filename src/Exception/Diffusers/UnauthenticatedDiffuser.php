<?php
	namespace Suphle\Exception\Diffusers;

	use Suphle\Contracts\{Exception\ExceptionHandler, Config\AuthContract, Presentation\BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\Format\{ Redirect, Json};

	use Suphle\Request\PayloadStorage;

	use Suphle\Exception\Explosives\Unauthenticated;

	use Suphle\Auth\Storage\TokenStorage;

	use Throwable;

	class UnauthenticatedDiffuser implements ExceptionHandler {

		private $renderer, $controllerAction = "virtualWall";

		/**
		 * @param {origin} Unauthenticated
		*/
		public function setContextualData (Throwable $origin):void {

			if ($origin->storageMechanism() instanceof TokenStorage)

				$this->renderer = $this->getTokenRenderer();

			else $this->renderer = $this->getSessionRenderer();
		}

		public function prepareRendererData ():void {

			$this->renderer->setHeaders(401, []);
		}

		public function getRenderer ():BaseRenderer {

			return $this->renderer;
		}

		protected function getTokenRenderer ():BaseRenderer {

			$renderer = new Json($this->controllerAction);

			return $renderer->setRawResponse([

				"message" => "Unauthenticated"
			]);
		}

		protected function getSessionRenderer ():BaseRenderer {

			return new Redirect($this->controllerAction, fn(RequestDetails $requestDetails, AuthContract $authContract, PayloadStorage $payloadStorage) => $authContract->markupRedirect() . "?". http_build_query([

					"path" => $requestDetails->getPath(),

					"query" => $payloadStorage->fullPayload()
				]));
		}
	}
?>