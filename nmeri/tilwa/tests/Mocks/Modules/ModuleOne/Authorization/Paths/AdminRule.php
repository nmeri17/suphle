<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

	use Tilwa\Request\RouteRule;

	class AdminRule extends RouteRule {

		public function permit ():bool {

			return $this->authorizedUser->isAdmin();
		}
	}
?>