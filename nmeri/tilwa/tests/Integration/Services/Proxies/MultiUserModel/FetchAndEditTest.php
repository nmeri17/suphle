<?php
	namespace Tilwa\Tests\Integration\Services\Proxies\MultiUserModel;

	use Tilwa\Services\{ Proxies\MultiUserModelCallProxy, Structures\OptionalDTO};

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, BaseDatabasePopulator};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{MultiUserEditMock, MultiUserEditError};

	use DateTime, DateInterval;

	class FetchAndEditTest extends IsolatedComponentTest {

		use DirectHttpTest, BaseDatabasePopulator, CommonBinds;

		private $modelName = MultiEditProduct::class;

		public function test_missing_key_on_update_throws_error () {

			$this->expectException(EditIntegrityException::class); // then

			$this->setHttpParams("/dummy", "put"); // given

			$this->container->getClass(MultiUserEditMock::class)

			->updateResource(); // when
		}

		protected function getActiveEntity ():string {

			return $this->modelName;
		}

		public function test_last_updater_invalidates_for_all_viewers () {

			$this->expectException(EditIntegrityException::class); // then

			$sutName = MultiUserEditMock::class;

			$threeMinutesAgo = (new DateTime)->sub(new DateInterval("PT3M"));

			$datedModel = $this->replicator->getBeforeInsertion(1, [

				IntegrityModel::INTEGRITY_COLUMN => $threeMinutesAgo
			])->first();

			// given
			$mock = $this->positiveDouble($sutName,

				["getResource" => $datedModel],
				[

					"updateResource" => [2, []]
				]
			);

			$this->setJsonParams("/dummy", [

				MultiUserModelCallProxy::INTEGRITY_COLUMN => $threeMinutesAgo,

				"name" => "nmeri"
			], "put");

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