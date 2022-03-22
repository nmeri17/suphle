<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\MultiUserModel;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Services\{Jobs\AddUserEditField, Proxies\MultiUserModelCallProxy, Structures\OptionalDTO};

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{MultiUserEditMock, MultiUserEditError};

	class FetchAndEditTest extends IsolatedComponentTest {

		use MockFacilitator, DirectHttpTest;

		public function test_get_queue_adds_integrity () {

			$sut = $this->positiveDouble(AddUserEditField::class, [], [ // using a stub to avoid injecting a live ormDialect

				"modelInstance" => $this->positiveDouble(IntegrityModel::class, [], [

					"addEditIntegrity" => [1, [$this->greaterThan(20)]]
				]) // then
			]); // given

			$sut->handle(); // when
		}

		public function test_missing_key_on_update_throws_error () {

			$this->expectException(EditIntegrityException::class); // then

			$this->setHttpParams("/dummy"); // given

			$this->container->getClass(MultiUserEditMock::class)

			->updateResource(); // when
		}

		public function test_last_updater_invalidates_for_all_viewers () {

			$this->expectException(EditIntegrityException::class); // then

			$sutName = MultiUserEditMock::class;

			// given
			$mock = $this->positiveDouble($sutName, [

				"getResource" => $this->createPartialMock(IntegrityModel::class, [

					"includesEditIntegrity" => true
				])
			], [

				"updateResource" => [2, [$this->anything()]]
			]); // we want to ensure it ran twice before throwing the error above

			$this->setHttpParams("/dummy", "put", json_encode([

				MultiUserModelCallProxy::INTEGRITY_COLUMN => 6556
			]));

			$sut = $this->container->whenTypeAny()->needsAny([

				$sutName => $mock
			])->getClass($sutName);

			for ($i = 0; $i < 2; $i++) $sut->updateResource();
		}

		public function test_update_can_withstand_errors () {

			$result = $this->container->getClass(MultiUserEditError::class)

			->updateResource(); // when

			$this->assertInstanceOf(OptionalDTO::class, $result); // then
		}
	}
?>