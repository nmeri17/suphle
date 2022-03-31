<?php
	namespace Tilwa\Auth\Renderers;

	use Tilwa\Contracts\Auth\{LoginRenderers, LoginActions};

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Response\Format\{ Redirect, Reload};

	use Tilwa\Auth\Repositories\BrowserAuthRepo;

	use Tilwa\Request\PayloadStorage;

	class BrowserLoginRenderer implements LoginRenderers {

		private $authService;

		protected $successDestination = "/";

		public function __construct (BrowserAuthRepo $authService) {

			$this->authService = $authService;
		}

		public function successRenderer ():BaseRenderer {

			return new Redirect( "successLogin", function (PayloadStorage $payloadStorage) {

				if ($payloadStorage->hasKey("path")) {

					$path = $payloadStorage->getKey("path");

					$queryPart = $payloadStorage->getKey("query");

					if (!empty($queryPart))

						$path .= "?" . $queryPart;

					return $path;
				}

				return $this->successDestination;
			});
		}

		public function failedRenderer ():BaseRenderer {

			return new Reload( "failedLogin");
		}

		public function getLoginService ():LoginActions {

			return $this->authService;
		}
	}
?>