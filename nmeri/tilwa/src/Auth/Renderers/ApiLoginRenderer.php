<?php
	namespace Tilwa\Auth\Renderers;

	use Tilwa\Contracts\Auth\{LoginRenderers, LoginActions};

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Response\Format\{ Redirect, Reload};

	use Tilwa\Auth\Repositories\ApiAuthRepo;

	class ApiLoginRenderer implements LoginRenderers {

		private $authService;

		public function __construct (ApiAuthRepo $authService) {

			$this->authService = $authService;
		}

		public function successRenderer ():BaseRenderer {

			return new Json( "successLogin");
		}

		public function failedRenderer ():BaseRenderer {

			return new Json( "failedLogin");
		}

		public function getLoginService ():LoginActions {

			return $this->authService;
		}
	}
?>