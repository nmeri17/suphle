<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\LoginRenderers;

	use Tilwa\Response\Format\{AbstractRenderer, Redirect, Reload};

	class BrowserLoginRenderer implements LoginRenderers {

		private $authService;

		public function __construct (EmailPasswordRepo $authService) {

			$this->authService = $authService;
		}

		public function successRenderer ():AbstractRenderer {

			return new Redirect( "successLogin", "/");
		}

		public function failedRenderer ():AbstractRenderer {

			return new Reload( "failedLogin", "/");
		}

		public function getLoginService ():LoginActions {

			return $this->authService;
		}
	}
?>