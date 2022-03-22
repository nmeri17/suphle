<?php
	namespace Tilwa\Exception\Diffusers;

	use Tilwa\Contracts\{Exception\ExceptionHandler, Config\Auth as AuthConfig};

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Response\Format\{AbstractRenderer, Redirect, Json};

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Exception\Explosives\Unauthenticated;

	use Tilwa\Auth\Storage\TokenStorage;

	class UnauthenticatedDiffuser implements ExceptionHandler {

		private $renderer, $controllerAction = "virtualWall";

		public function setContextualData (Unauthenticated $origin):void {

			if ($origin->storageMechanism() instanceof TokenStorage)

				$this->renderer = $this->getTokenRenderer();

			else $this->renderer = $this->getSessionRenderer();
		}

		public function prepareRendererData ():void {

			$this->renderer->setHeaders(401, []);
		}

		public function getRenderer ():AbstractRenderer {

			return $this->renderer;
		}

		protected function getTokenRenderer ():AbstractRenderer {

			$renderer = new Json($this->controllerAction);

			return $renderer->setRawResponse([

				"message" => "Unauthenticated"
			]);
		}

		protected function getSessionRenderer ():AbstractRenderer {

			return new Redirect($this->controllerAction, function (RequestDetails $requestDetails, AuthConfig $authConfig, PayloadStorage $payloadStorage) {

				return $authConfig->markupRedirect() . "?". http_build_query([

					"path" => $requestDetails->getPath(),

					"query" => $payloadStorage->fullPayload()
				]);
			});
		}
	}
?>