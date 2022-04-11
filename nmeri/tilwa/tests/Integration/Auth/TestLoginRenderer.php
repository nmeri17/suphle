<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Response\Format\Markup;

	use Tilwa\Testing\{ Condiments\BaseDatabasePopulator, TestTypes\IsolatedComponentTest, Proxies\SecureUserAssertions };

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

			return EloquentUser::class;
		}

		protected function getLoginResponse () {

			$routerName = RouteManager::class;

			$this->container->whenTypeAny()->needsAny([

				$routerName => $this->positiveDouble($routerName, [

					"getPreviousRenderer" => $this->positiveDouble(Markup::class)
				]) // since we're just sending a post request without an initial get
			])
			->getClass(ModuleLoginHandler::class)->getResponse();
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