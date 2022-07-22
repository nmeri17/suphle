<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Controllers\BaseController;

	use Suphle\Response\Format\Json;

	class BrowserCollection extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}

		public function MODULE__TWOh_id() {

			$this->_get(new Json("checkPlaceholder"));
		}
	}
?>