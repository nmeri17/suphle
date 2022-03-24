<?php
	namespace Tilwa\Tests\Integration\Routing\Canaries;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions};

	use Tilwa\Contracts\Auth\User;

	class CanaryUserTest extends TestsRouter {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		/**
		 * This should be true since canary is evaluated before collection storage method is derived (which is done after all segments match)
	     * @dataProvider getUserAndResult
	     */
		public function test_injecting_authStorage_uses_its_default_not_collection_auth (User $user, string $handlerName) {

			$this->actingAs($user); // given

			$matchingRenderer = $this->fakeRequest("/special-foo/same-url"); // when

			$this->assertTrue($matchingRenderer->matchesHandler($handlerName) ); // then
		}

		protected function getUserAndResult ():array {

			return [

				[$this->container->getClass(User::class)->find(5), "user5Handler"],

				[$this->container->getClass(User::class)->find(4), "fooHandler"]
			];
		}
	}
?>