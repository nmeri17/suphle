<?php
	namespace Suphle\Tests\Integration\Orms;

	use Suphle\Contracts\{Services\Models\IntegrityModel, Config\Router};

	use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\Auth\UnlocksAuthorization1};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	// this group of tests should run together rather than individually
	class FrozenStateTest extends ModuleLevelTest {

		use BaseDatabasePopulator;

		private const NUM_TO_INSERT = 20, TABLE_NAME = "employment";

		private $lastInserted, $updatePayload = ["salary" => 850_000];

		protected $debugCaughtExceptions = true;

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => UnlocksAuthorization1::class
					]);
				})
			];
		}

		public function preDatabaseFreeze ():void {

			$this->lastInserted = $this->replicator->modifyInsertion(self::NUM_TO_INSERT)[0];
		}

		public function test_reverts_to_frozen_state_after_reset ():int {

			$this->assertSame( // given

				$this->getInitialCount() + self::NUM_TO_INSERT,

				$this->replicator->getCount()
			);

			$csrfToken = $this->getContainer()->getClass(CsrfGenerator::class)
			->newToken();

			$this->putJson( // without this, payload will be stored on POST and saved improperly for reads

				"/pmulti-edit/" . $this->lastInserted->id,

				array_merge($this->updatePayload, [

					CsrfGenerator::TOKEN_FIELD => $csrfToken,

					MultiUserEditHandler::INTEGRITY_KEY => $this->lastInserted
					->toArray()[IntegrityModel::INTEGRITY_COLUMN] // force casting from carbon type to string
				])
			); // when

			//$this->establishConnectionVariables();

			// then
			$this->assertSame(

				self::NUM_TO_INSERT, $this->replicator->getCount()
			);$x = $this->replicator->getExistingEntities(

				100, $this->updatePayload
			);
var_dump(87, $x);
			$this->assertCount(1, $x); // fetch 100 and assert only one was modified

			return $this->lastInserted->id; // since it would've been overriden by the next iteration
		}

		/**
		 * @depends test_reverts_to_frozen_state_after_reset
		*/
		public function test_updates_were_rolled_back (int $previousRequestId) {

			$this->databaseApi->assertDatabaseMissing(

				self::TABLE_NAME, array_merge($this->updatePayload, [

					"id" => $previousRequestId
				])
			);
		}

		/**
		 * @depends test_updates_were_rolled_back
		*/
		public function test_will_see_leftover_from_previous_seeding () {

			$numTestsBeforeThis = 2 + 1 /*this*/;

			$this->assertSame(// then

				$this->getInitialCount() + (self::NUM_TO_INSERT * $numTestsBeforeThis),

				$this->replicator->getCount()
			);
		}
	}
?>