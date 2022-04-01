<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Testing\{ Condiments\BaseDatabasePopulator, TestTypes\IsolatedComponentTest };

	use Tilwa\Testing\Proxies\SecureUserAssertions;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class TestLoginRenderer extends IsolatedComponentTest {

		use BaseDatabasePopulator, UserInserter, CommonBinds, SecureUserAssertions {

			BaseDatabasePopulator::setUp as databasePopulatorSetup;
		}

		protected $loginRendererName;

		protected function setUp ():void {

			$this->databasePopulatorSetup();
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		protected function getLoginResponse () {

			$this->container->getClass(ModuleLoginHandler::class)->getResponse();
		}

		protected function injectLoginRenderer (int $successCount, int $failureCount):void {

			$this->container->whenTypeAny()->needsAny([

				$this->loginRendererName => $this->negativeDouble($this->loginRendererName, [], [

					"successRenderer" => [$successCount, []],

					"failedRenderer" => [$failureCount, []]
				])
			]);
		}
	}
?>