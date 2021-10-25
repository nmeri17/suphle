<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CrudRoutes;

	class CrudTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return CrudRoutes::class;
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

		public function test_collection_requires_prefix () {

			// confirm setting neither creates no crud routes
		}

		public function test_authentication_catches_crud_routes () {

			//
		}
	}
?>