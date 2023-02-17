<?php
	namespace Suphle\Tests\Integration\Routing\Canaries;

	use Suphle\Contracts\Auth\UserContract;

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryRoutes;

	use Suphle\Tests\Integration\Routing\TestsRouter;

	class CanaryUserTest extends TestsRouter {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getEntryCollection ():string {

			return CanaryRoutes::class;
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		/**
		 * This should be true since canaries use authStorge received from parent collection
	     */
		public function test_canaries_use_collection_auth () {

			$user = $this->replicator->getSpecificEntities(1, ["id" => 5])[0];

			// default = sessionStorage
			$this->actingAs($user); // given

			$matchingRenderer = $this->fakeRequest("/special-foo/same-url"); // when

			$this->assertNull($matchingRenderer);

			$this->assertGuest(TokenStorage::class); // then
		}
	}
?>