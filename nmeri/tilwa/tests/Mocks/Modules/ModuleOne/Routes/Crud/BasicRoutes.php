<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	class BasicRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}
		
		public function SAVE__ALLh () {
			
			$this->_crud("envelope")->save();
		}
		
		public function DISABLE__SOMEh () {
			
			$this->_crud("handicap")->disableHandlers(["saveNew"])

			->save();
		}
		
		public function OVERRIDE () {
			
			$this->_crud("usurp")

			->replaceShowOne(new Markup("myOverride", "usurp/show-one"))

			->save();
		}
		
		public function NON__EXISTENTh () {
			
			$this->_crud("missing")

			->replaceFooBar(new Markup("atLarge", "missing/show-one"))

			->save();
		}
	}
?>