<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Request\PathAuthorizer;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\BaseCoordinator, Authorization\Paths\AdminRule};

	#[HandlingCoordinator(BaseCoordinator::class)]
	class AuthorizeRoutes extends BaseCollection {

		public function ADMIN__ENTRYh () {

			$this->_get(new Json("plainSegment"));
		}

		public function ADMIN () {

			$this->_prefixFor(UnlocksAuthorization1::class);
		}

		public function _authorizePaths (PathAuthorizer $pathAuthorizer):void {

			$pathAuthorizer->addRule (

				[ "ADMIN__ENTRYh", "ADMIN"], AdminRule::class
			);
		}
	}
?>