<?php
	namespace Tilwa\Tests\Integration\Routing\Crud;

	use Tilwa\Tests\Integration\Routing\BaseRouterTest;

	use Tilwa\Testing\{Proxies\FrontDoorTest, Condiments\PopulatesDatabaseTest};

	use Tilwa\Tests\Mocks\Modules\ModuleFour\ModuleFourDescriptor;

	use Tilwa\Contracts\Auth\User;

	use Tilwa\App\Container;

	use PHPUnit\Framework\TestCase;

	class AuthSecureTest extends TestCase {

		use FrontDoorTest, PopulatesDatabaseTest;

		protected function getModules():array {

			return [new ModuleFourDescriptor(new Container)];
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_no_authenticated_user_throws_error () {

			$this->get("/secure-some/edit/5") // when

			->assertUnauthorized(); // then
		}

		public function test_with_authentication_throws_no_error () {

			$this->actingAs($this->getRandomEntity()) // given

			->get("/secure-some/edit/5") // when

			->assertOk(); // then
		}
	}
?>