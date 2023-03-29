<?php
	namespace Suphle\Tests\Integration\Orms;

	use Suphle\Contracts\{Services\Models\IntegrityModel, Config\Router};

	use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, SecureUserAssertions};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\Auth\UnlocksAuthorization1};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	// this group of tests should run together rather than individually
	class SingleModuleRetainTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		private const TABLE_NAME = "employment";

		private Employment $lastInserted;
		
		private array $updatePayload = ["salary" => 850_000];

		protected bool $debugCaughtExceptions = true;

		protected bool $muffleExceptionBroadcast = false;

		protected function setUp ():void {

			$this->databaseAllSetup();

			$this->lastInserted = $this->replicator->getRandomEntity();
		}

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

		public function test_retains_seeded_data_after_request ():array {

			$seededAmount = $this->replicator->getCount(); // given

			// for the edit history bits
			$this->actingAs($this->lastInserted->employer->user);

			$this->assertSuccessfulEditUpdate(); // when

			// then
			$this->assertSame(

				$seededAmount, $this->replicator->getCount()
			); // still visible

			$modifiedRows = $this->replicator->getSpecificEntities(

				100, array_merge($this->updatePayload, [

					"id" => $this->lastInserted->id // the main assertion here -- that this row is retained
				])
			);

			$this->assertCount(1, $modifiedRows); // fetch 100 and assert that truly, one was modified

			return [

				"previous_request_id" => $this->lastInserted->id, // since {lastInserted} would've been overriden by the next iteration
				"started_with" => $seededAmount - $this->getInitialCount()
			];
		}

		protected function assertSuccessfulEditUpdate ():void {

			$csrfToken = $this->getContainer()->getClass(CsrfGenerator::class)
			->newToken();

			$this->put(

				"/pmulti-edit/" . $this->lastInserted->id,

				array_merge($this->updatePayload, [

					CsrfGenerator::TOKEN_FIELD => $csrfToken,

					MultiUserEditHandler::INTEGRITY_KEY => $this->lastInserted
					->toArray()[IntegrityModel::INTEGRITY_COLUMN] // force casting from carbon type to string
				])
			) // when
			->assertJsonPath("message", 1); // sanity check // update success
		}

		/**
		 * Passes because the test's transaction is expected to be rolled back after test completes
		 * 
		 * @depends test_retains_seeded_data_after_request
		*/
		public function test_rolls_back_preceding_test_updates (array $testDetails):int {

			$this->databaseApi->assertDatabaseMissing(

				self::TABLE_NAME, array_merge($this->updatePayload, [

					"id" => $testDetails["previous_request_id"]
				])
			);

			return $testDetails["started_with"];
		}

		/**
		 * @depends test_rolls_back_preceding_test_updates
		*/
		public function test_will_not_see_leftover_from_previous_seedings (int $startedWith) {

			$this->assertSame(// then

				$startedWith + $this->getInitialCount(),

				$this->replicator->getCount()
			);
		}
	}
?>