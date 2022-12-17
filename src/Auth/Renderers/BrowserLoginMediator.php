<?php
	namespace Suphle\Auth\Renderers;

	use Suphle\Contracts\Auth\{LoginFlowMediator, LoginActions};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Response\Format\{ Redirect, Reload};

	use Suphle\Auth\Repositories\BrowserAuthRepo;

	use Suphle\Request\PayloadStorage;

	class BrowserLoginMediator implements LoginFlowMediator {

		protected $successDestination = "/";

		public function __construct(private readonly BrowserAuthRepo $authService) {

			//
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