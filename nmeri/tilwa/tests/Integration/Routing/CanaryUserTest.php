<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	use Tilwa\Testing\Condiments\{PopulatesDatabaseTest, IsolatedComponentSecurity};

	use Tilwa\Contracts\Auth\User;

	class CanaryUserTest extends BaseRouterTest {

		use PopulatesDatabaseTest, IsolatedComponentSecurity;

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

			$matchingRenderer = $this->fakeRequest("/special-foo/same-url");

			$this->assertSame($matchingRenderer->getHandler(), $handlerName);
		}

		protected function getUserAndResult ():array {

			return [

				[$this->container->getClass(User::class)->find(5), "user5Handler"],

				[$this->container->getClass(User::class)->find(4), "fooHandler"]
			];
		}
	}
?>