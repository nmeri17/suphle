<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\{DefaultCanary, InvalidCanary, CanaryRequestHasFoo, CanaryForUser5};

	use Suphle\Routing\BaseApiCollection;

	class CanaryRoutes extends BaseApiCollection { // try with/without prefix, with/without middleware, with/without auth

		public function _handlingClass ():string {

			return "";
		}
		
		public function LOAD__DEFAULTh () {
			
			$this->_canaryEntry([

				InvalidCanary::class, DefaultCanary::class
			]);
		}
		
		public function OTHER__USERS__SKIPh () {
			
			$this->_canaryEntry([
				InvalidCanary::class,

				CanaryForUser5::class, DefaultCanary::class
			]);
		}

		public function SPECIAL__FOOh () {

			$this->_canaryEntry([

				CanaryForUser5::class, CanaryRequestHasFoo::class
			]);
		}

		public function NONE__PASSINGh () {

			$this->_canaryEntry([InvalidCanary::class]);
		}
	}
?>