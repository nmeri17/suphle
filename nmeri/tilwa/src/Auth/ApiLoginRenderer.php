<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\LoginRenderers;

	use Tilwa\Response\Format\{AbstractRenderer, Json};

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