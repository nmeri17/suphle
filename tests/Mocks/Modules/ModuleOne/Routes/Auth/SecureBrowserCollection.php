<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\BaseCoordinator;

	use Suphle\Response\Format\Json;

	#[HandlingCoordinator(BaseCoordinator::class)]
	class SecureBrowserCollection extends BaseCollection {

		public function SEGMENT() {

			$this->_get(new Json("plainSegment"));
		}

		public function _authenticatedPaths():array {

			return ["SEGMENT"];
		}
	}
?>