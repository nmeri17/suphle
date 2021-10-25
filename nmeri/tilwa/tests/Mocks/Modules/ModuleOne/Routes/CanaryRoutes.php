<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\{DefaultCanary, InvalidCanary, CanaryRequestHasFoo, CanaryForUser5};

	use Tilwa\Routing\BaseCollection;

	class CanaryRoutes extends BaseCollection { // try with/without prefix, with/without middleware, with/without auth

		public function __construct(CanaryValidator $validator, TokenStorage $authStorage) {

			$this->validator = $validator;

			$this->authStorage = $authStorage;
		}

		public function _handlingClass ():string {

			return "";
		}
		
		public function LOAD__DEFAULTh () {
			
			return $this->_canaryEntry([

				InvalidCanary::class, DefaultCanary::class
			]);
		}
		
		public function OTHER__USERS__SKIPh () {
			
			return $this->_canaryEntry([
				InvalidCanary::class,

				CanaryForUser5::class, DefaultCanary::class
			]);
		}

		public function SPECIAL__FOOh () {

			return $this->_canaryEntry([

				CanaryForUser5::class, CanaryRequestHasFoo::class
			]);
		}

		public function NONE__PASSINGh () {

			return $this->_canaryEntry([InvalidCanary::class]);
		}
	}
?>