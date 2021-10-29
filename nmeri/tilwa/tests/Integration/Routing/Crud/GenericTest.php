<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Tests\Integration\Routing\BaseRouterTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud\BasicRoutes;

	class GenericTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return BasicRoutes::class;
		}

		public function test_can_find_all_routes () {
			
			// needs dataProvider
		}

		public function test_can_disable_routes () {
			
			// 
		}

		public function test_can_override_routes () {
			
			// 
		}
	}
?>