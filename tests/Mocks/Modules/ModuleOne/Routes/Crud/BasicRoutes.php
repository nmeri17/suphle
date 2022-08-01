<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Suphle\Routing\{BaseCollection, Crud\BrowserBuilder};

	use Suphle\Response\Format\Markup;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Controllers\CrudController;

	class BasicRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return CrudController::class;
		}
		
		public function SAVE__ALLh () {
			
			$this->_crud("envelope")->registerCruds();
		}
		
		public function DISABLE__SOMEh () {
			
			$this->_crud("handicap")->disableHandlers([BrowserBuilder::SAVE_NEW])

			->registerCruds();
		}
		
		public function OVERRIDE () {
			
			$this->_crud("usurp")->replaceRenderer(

				BrowserBuilder::SHOW_ONE,

				new Markup("myOverride", "usurp/show-one")
			)
			->registerCruds();
		}
		
		public function NON__EXISTENTh () {
			
			$this->_crud("missing")

			->replaceRenderer("fooBar", new Markup("atLarge", "missing/show-one"))

			->registerCruds();
		}
	}
?>