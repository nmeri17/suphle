<?php
	namespace Suphle\Tests\Integration\Auth;

	use Suphle\Contracts\{Auth\AuthStorage, Config\Router};

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor,Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\{V1\LowerMirror, V2\ApiUpdate2Entry};

	class BrowserVersioningTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

							"apiStack" => [

								"v2" => ApiUpdate2Entry::class,

								"v1" => LowerMirror::class
							]
						]
					);
				})
			];
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		public function test_original_pattern_requires_auth () {

			// given no given user

			$this->get("/api/v1/cascade") // when

			->assertUnauthorized(); // then
		}

		/**
		 * This is expected behavior since lower ones are not loaded and thus can't know its auth requirements
		 * 
		 * @depends test_original_pattern_requires_auth
		*/
		public function test_overriden_pattern_doesnt_require_auth () {

			// given no given user

			$responseAsserter = $this->get("/api/v2/cascade"); // when

			$responseAsserter->assertOk(); // then
		}
	}
?>