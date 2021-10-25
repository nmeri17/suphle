<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Routes;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Controllers\BaseController;

	use Tilwa\Response\Format\Json;

	class BrowserCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function MODULE__TWOh_id() {

			$this->_get(new Json("checkPlaceholder"));
		}
	}
?>