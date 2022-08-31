<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Routing\BaseCollection;

	use Suphle\Response\Format\Json;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\ValidatorController;

	class ValidatorCollection extends BaseCollection {

		public function _handlingClass ():string {

			return ValidatorController::class;
		}

		public function POST__WITHh () {

			$this->_post(new Json("postWithValidator"));
		}

		public function POST__WITHOUTh () {

			$this->_post(new Json("postNoValidator"));
		}

		public function GET__WITHOUTh () {

			$this->_get(new Json("handleGet"));
		}
	}
?>