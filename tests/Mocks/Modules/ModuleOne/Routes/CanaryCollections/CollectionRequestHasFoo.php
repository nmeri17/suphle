<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

	use Suphle\Routing\BaseCollection;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\CanaryController;

	use Suphle\Response\Format\Json;

	class CollectionRequestHasFoo extends BaseCollection {

		public function _handlingClass ():string {

			return CanaryController::class;
		}

		public function SAME__URLh () {

			$this->_get(new Json("fooHandler"));
		}
	}
?>