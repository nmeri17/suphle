<?php
	namespace Tilwa\Tests\Integration\Routing;

	class CrudTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
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