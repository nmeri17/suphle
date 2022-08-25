<?php
	namespace Suphle\Auth\Renderers;

	use Suphle\Contracts\Auth\{LoginFlowMediator, LoginActions};

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Response\Format\Json;

	use Suphle\Auth\Repositories\ApiAuthRepo;

	class ApiLoginMediator implements LoginFlowMediator {

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