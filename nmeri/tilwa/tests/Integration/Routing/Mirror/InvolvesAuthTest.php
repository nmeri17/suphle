<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Testing\{Condiments\PopulatesDatabaseTest, Proxies\FrontDoorTest};

	use Tilwa\Contracts\Auth\User;

	use Tilwa\Auth\Storage\TokenStorage;

	use Tilwa\Tests\Mocks\Modules\ModuleFive\ModuleFiveDescriptor;

	use Tilwa\App\Container;

	use PHPUnit\Framework\TestCase;

	class InvolvesAuthTest extends TestCase {

		use FrontDoorTest, PopulatesDatabaseTest;

		protected function getModules():array {

			return [new ModuleFiveDescriptor(new Container)];
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		public function test_auth_storage_changes () {

			$tokenClass = TokenStorage::class;

			$this->actingAs($this->getRandomEntity(), $tokenClass); // given

			$this->get("/api/v1/segment") // when

			->assertOk(); // then

			$this->assertInstanceOf($tokenClass, $this->getAuthStorage());
		}
	}
?>