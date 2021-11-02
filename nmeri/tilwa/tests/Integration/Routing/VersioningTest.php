<?php
	namespace Tilwa\Tests\Integration\Routing;

	class VersioningTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		public function test_can_get_content_at_specific_version () {

			// 
		}

		public function test_can_override_route_in_lower_version () {

			// 
		}

		public function test_top_level_content_not_exist_when_request_lower_version () {

			//
		}
	}
?>