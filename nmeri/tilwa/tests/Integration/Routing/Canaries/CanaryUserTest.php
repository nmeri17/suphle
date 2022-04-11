<?php
	namespace Tilwa\Tests\Integration\Routing\Canaries;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions};

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	class CanaryUserTest extends TestsRouter {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		/**
		 * This should be true since canary is evaluated before collection storage method is derived (which is done after all segments match)
	     */
		public function test_injecting_authStorage_uses_its_default_not_collection_auth (User $user, string $handlerName) {

			$this->dataProvider([

				[$this, "getUserAndResult"]
			], function () {

				$this->actingAs($user); // given

				$matchingRenderer = $this->fakeRequest("/special-foo/same-url"); // when

				$this->assertTrue($matchingRenderer->matchesHandler($handlerName) ); // then
			});
		}

		public function getUserAndResult ():array {

			return [

				[$this->container->getClass(User::class)->find(5), "user5Handler"],

				[$this->container->getClass(User::class)->find(4), "fooHandler"]
			];
		}
	}
?>