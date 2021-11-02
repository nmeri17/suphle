<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	class MirrorActivated extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		public function test_can_switch_to_api_collection () {

			$matchingRenderer = $this->fakeRequest("/api/v1/api-segment"); // when

			$this->assertTrue($matchingRenderer->matchesHandler("segmentHandler")); // then
		}

		public function test_can_detect_browser_route () {

			$matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

			$this->assertTrue($matchingRenderer->matchesHandler("plainSegment")); // then
		}

		public function test_can_override_browser_route () {

			$matchingRenderer = $this->fakeRequest("/api/v1/segment/5"); // when

			$this->assertTrue($matchingRenderer->matchesHandler("simplePairOverride")); // then
		}

		public function test_auth_storage_changes () {

			//
		}
	}
?>