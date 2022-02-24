<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Adapters\Orms\Eloquent\Models\User;

	use Tilwa\Testing\{Condiments\PopulatesDatabaseTest, TestTypes\IsolatedComponentTest, Proxies\ExaminesHttpResponse};

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Illuminate\Testing\TestResponse;

	class LoginRepoTest extends IsolatedComponentTest {

		use PopulatesDatabaseTest, ExaminesHttpResponse, UserInserter;

		protected function getActiveEntity ():string {

			return User::class;
		}

		protected function getLoginResponse ():TestResponse {

			$identifier = $this->container->getClass(ModuleLoginHandler::class);

			$identifier->getResponse();

			return $this->makeExaminable($identifier->handlingRenderer()); // using this to streamline comparison between json response and our expected value
		}
	}
?>