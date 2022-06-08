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

			$defaultPath = $this->successDestination;

			return new Redirect( "successLogin", function (PayloadStorage $payloadStorage) use ($defaultPath) {

				if (!$payloadStorage->hasKey("path"))

					return $defaultPath;

				$path = $payloadStorage->getKey("path");

				$queryPart = $payloadStorage->getKey("query");

				if (!empty($queryPart))

					$path .= "?" . $queryPart;

				return $path;
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