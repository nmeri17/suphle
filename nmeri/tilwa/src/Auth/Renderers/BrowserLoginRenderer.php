<?php
	namespace Tilwa\Auth\Renderers;

	use Tilwa\Contracts\Auth\{LoginRenderers, LoginActions};

	use Tilwa\Response\Format\{AbstractRenderer, Redirect, Reload};

	use Tilwa\Auth\Repositories\BrowserAuthRepo;

	class BrowserLoginRenderer implements LoginRenderers {

		private $authService;

		public function __construct (BrowserAuthRepo $authService) {

			$this->authService = $authService;
		}

		public function successRenderer ():AbstractRenderer {

			return new Redirect( "successLogin", function () {

				return "/";
			});
		}

		public function failedRenderer ():AbstractRenderer {

			return new Reload( "failedLogin");
		}

		public function getLoginService ():LoginActions {

			return $this->authService;
		}
	}
?>