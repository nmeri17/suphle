<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Config\Router as IRouter;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Config\RouterMock};

	class TestsRouter extends IsolatedComponentTest {

		use DirectHttpTest;

		public function getRouter ():RouteManager {

			return $this->container->getClass(RouteManager::class);
		}

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function concreteBinds ():array {

			return [

				IRouter::class => $this->positiveDouble(
					RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection()
					]
				)
			];
		}

		/**
		 * Use in tests involving pulling path from requestDetails
		*/
		protected function fakeRequest (string $url, string $httpMethod = "get"):AbstractRenderer {

			$this->setHttpParams($url, $httpMethod);

			$router = $this->getRouter();

			$router->findRenderer();

			return $router->getActiveRenderer();
		}
	}
?>