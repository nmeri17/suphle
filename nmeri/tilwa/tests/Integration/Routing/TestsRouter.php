<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\Contracts\Config\{Router as RouterContract, ModuleFiles};

	use Tilwa\Contracts\Presentation\BaseRenderer;

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

				RouterContract::class => $this->positiveDouble(
					RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection()
					]
				),

				ModuleFiles::class => new AscendingHierarchy ("Tilwa\Tests\Mocks\Modules\ModuleOne\Routes")
			];
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