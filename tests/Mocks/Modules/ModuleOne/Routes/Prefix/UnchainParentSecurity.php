<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix;

	use Suphle\Routing\{BaseCollection, PreMiddlewareRegistry, Decorators\HandlingCoordinator};

	use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\MixedNestedSecuredController;

	#[HandlingCoordinator(MixedNestedSecuredController::class)]
	class UnchainParentSecurity extends BaseCollection {

		public function _preMiddleware (PreMiddlewareRegistry $registry):void {

			$registry->removeTag(

				["UNLINK"], AuthenticateMetaFunnel::class
			);
		}
		
		public function UNLINK () {
			
			$this->_httpGet(new Json("handleUnlinked"));
		}
		
		public function RETAIN__AUTHh () {
			
			$this->_httpGet(new Json("handleRetained"));
		}
	}
?>