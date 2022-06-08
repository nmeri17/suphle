<?php
	namespace Tilwa\Tests\Integration\Routing\Canaries;

	use Tilwa\Exception\Explosives\Generic\InvalidImplementor;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	class CanaryTest extends TestsRouter {

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		public function test_will_fail_on_invalid_canaries () {

			$this->expectException(InvalidImplementor::class);

			$this->fakeRequest("/load-default/same-url"); // when
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

			$matchingRenderer = $this->fakeRequest("/special-foo/same-url?foo=32");

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler("fooHandler"));
		}
	}
?>