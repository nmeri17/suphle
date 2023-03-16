<?php
	namespace Suphle\Tests\Integration\Routing\Canaries;

	use Suphle\Exception\Explosives\{NotFoundException, DevError\InvalidImplementor};

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	class CanaryTest extends TestsRouter {

		protected bool $debugCaughtExceptions = true;

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		public function test_will_fail_on_invalid_canaries () {

			$this->expectException(InvalidImplementor::class);

			$this->fakeRequest("/load-default/same-url"); // when
		}

		public function test_no_matching_canary_will_throw_not_found () {

			$this->expectException(NotFoundException::class);

			$matchingRenderer = $this->fakeRequest("/special-foo");
		}

		public function test_can_hydrate_and_evaluate_dependencies () {

			$matchingRenderer = $this->fakeRequest("/special-foo/same-url?foo=32");

			$this->assertNotNull($matchingRenderer);

			$this->assertTrue($matchingRenderer->matchesHandler("fooHandler"));
		}
	}
?>