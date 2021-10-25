<?php
	namespace Tilwa\Auth\Renderers;

	use Tilwa\Contracts\Auth\{LoginRenderers, LoginActions};

	use Tilwa\Response\Format\{AbstractRenderer, Json};

	use Tilwa\Auth\Repositories\ApiAuthRepo;

	class ApiLoginRenderer implements LoginRenderers {

		private $authService;

		public function __construct (ApiAuthRepo $authService) {

			$this->authService = $authService;
		}

		public function successRenderer ():AbstractRenderer {

			return new Json( "successLogin");
		}

		public function failedRenderer ():AbstractRenderer {

			return new Json( "failedLogin");
		}

		public function getLoginService ():LoginActions {

			return $this->authService;
		}
	}
?>