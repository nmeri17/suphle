<?php
	namespace Suphle\Auth\Renderers;

	use Suphle\Contracts\Auth\{LoginRenderers, LoginActions};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Response\Format\{ Redirect, Reload};

	use Suphle\Auth\Repositories\BrowserAuthRepo;

	use Suphle\Request\PayloadStorage;

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