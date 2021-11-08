<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Config\RouterMock};

	use Tilwa\Contracts\Config\Router as IRouter;

	class BaseRouterTest extends IsolatedComponentTest {

		use DirectHttpTest, MockFacilitator;

		public function getRouter ():RouteManager {

			return $this->container->getClass(RouteManager::class);
		}

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function entityBindings ():self {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => $this->positiveMock(
					RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection()
					]
				)
			]);

			return $this;
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