<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Auth;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class AuthorizeRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function SEGMENT () {

			$this->_get(new Json("plainSegment"));
		}

		public function _authorizePaths ():void {

			$this->pathAuthorizer->addRule ([ $patterns], $rule); // see Tilwa\Request\RouteRule
		}
	}
?>