<?php
	namespace Tilwa\Tests\Integration\Routing\Canaries;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	class CanaryTest extends BaseRouterTest {

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		/**
	     * @dataProvider pathsToValidCanaries
	     */
		public function test_will_filter_invalid_canaries (string $segment, string $handler) {

			$matchingRenderer = $this->fakeRequest("/load-default/$segment"); // when

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler($handler) ); // then
		}

		public function pathsToValidCanaries ():array {

			return [
				["/same-url", "defaultHandler"],

				["/5", "defaultPlaceholder"]
			];
		}

		/**
	     * @dataProvider pathsToEmptyCanaries
	     */
		public function test_all_invalid_skips_pattern (string $url) {

			$matchingRenderer = $this->fakeRequest($url);

			$this->assertNull($matchingRenderer);
		}

		public function pathsToEmptyCanaries ():array {

			return [
				["/none-passing"],

				["/special-foo"]
			];
		}

		public function test_can_hydrate_and_evaluate_dependencies () {

			$matchingRenderer = $this->fakeRequest("/special-foo?foo=32");

			$this->assertSame($matchingRenderer, "fooHandler");
		}
	}
?>