<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Testing\{Condiments\BaseDatabasePopulator, TestTypes\IsolatedComponentTest };

	use Tilwa\Testing\Proxies\{ExaminesHttpResponse, Extensions\TestResponseBridge};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class TestLoginRepo extends IsolatedComponentTest {

		use BaseDatabasePopulator, ExaminesHttpResponse, UserInserter, CommonBinds {

			BaseDatabasePopulator::setUp as databasePopulatorSetup;
		}

		protected function setUp ():void {

			$this->databasePopulatorSetup();

			$this->massProvideSession();
		}

		protected function getActiveEntity ():string {

			return User::class;
		}

		protected function getLoginResponse ():TestResponseBridge {

			$identifier = $this->container->getClass(ModuleLoginHandler::class);

			$identifier->getResponse();

			return $this->makeExaminable($identifier->handlingRenderer()); // using this to streamline comparison between json response and our expected value
		}
	}
?>