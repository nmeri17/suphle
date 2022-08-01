<?php
	namespace Suphle\Tests\Integration\Routing;

	use Suphle\Routing\RouteManager;

	use Suphle\Contracts\{Config\Router as RouterContract, Presentation\BaseRenderer};

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Config\RouterMock};

	class TestsRouter extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds {

			CommonBinds::concreteBinds as commonConcretes;

			CommonBinds::simpleBinds as commonSimples;
		}

		public function getRouter ():RouteManager {

			return $this->container->getClass(RouteManager::class);
		}

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function concreteBinds ():array {

			return array_merge($this->commonConcretes(), [

				RouterContract::class => $this->positiveDouble(
					RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection()
					]
				)
			]);
		}

		protected function simpleBinds ():array {

			$commonSimples = $this->commonSimples();

			unset($commonSimples[RouterContract::class]);

			return $commonSimples;
		}

		/**
		 * Use in tests involving pulling path from requestDetails
		*/
		protected function fakeRequest (string $url, string $httpMethod = "get"):?BaseRenderer {

			$this->setHttpParams($url, $httpMethod);

			$router = $this->getRouter();

			$router->findRenderer();

			return $router->getActiveRenderer();
		}
	}
?>