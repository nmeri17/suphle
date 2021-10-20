<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Config\RouterMock};

	use Tilwa\Contracts\Config\Router as IRouter;

	class BaseRouterTest extends IsolatedComponentTest {

		use DirectHttpTest;

		public function getRouter ():RouteManager {

			return $this->container->getClass(RouteManager::class);
		}

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function entityBindings ():self {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => new RouterMock($this->getEntryCollection())
			]);

			return $this;
		}

		protected function fakeRequest (string $url):AbstractRenderer {

			$this->setHttpParams($url); // this should be the first line in all the tests involving pulling path from requestDetails?

			$router = $this->getRouter();

			$router->findRenderer();

			return $router->getActiveRenderer();
		}
	}
?>