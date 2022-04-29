<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Contracts\{Auth\ModuleLoginHandler, Presentation\BaseRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Testing\{ Condiments\BaseDatabasePopulator, TestTypes\IsolatedComponentTest, Proxies\SecureUserAssertions };

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class TestLoginRenderer extends IsolatedComponentTest {

		use BaseDatabasePopulator, UserInserter, CommonBinds, SecureUserAssertions {

			CommonBinds::concreteBinds as commonConcretes;
		}

		protected $loginRendererName, $loginRepoService;

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		protected function concreteBinds ():array {

			$routerName = RouteManager::class;

			return array_merge($this->commonConcretes(), [

				$routerName => $this->replaceConstructorArguments($routerName, [], [

					"getPreviousRenderer" => $this->positiveDouble(BaseRenderer::class ) // since we're just sending a post request without an initial get
				])
			]);
		}

		protected function evaluateLoginStatus ():void {

			$this->container->getClass(ModuleLoginHandler::class)->setResponseRenderer();
		}

		protected function injectLoginRenderer (int $successCount, int $failureCount):void {

			$localLoginManager = $this->replaceConstructorArguments(

				$this->loginRendererName, [

					"authService" => $this->container->getClass($this->loginRepoService)
				], [], [

					"successRenderer" => [$successCount, []],

					"failedRenderer" => [$failureCount, []]
				]
			);

			$this->container->whenTypeAny()->needsAny([

				$this->loginRendererName => $localLoginManager
			]);
		}
	}
?>