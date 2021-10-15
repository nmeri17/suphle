<?php

	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Testing\IsolatedComponentTest;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	use Tilwa\Contracts\Config\Router as IRouter;

	class BaseRouterTest extends IsolatedComponentTest {

		public function getRouter ():RouteManager {

			return $this->container->getClass(RouteManager::class);
		}

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function bindEntities ():self {

			parent::bindEntities();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => new RouterMock($this->getEntryCollection())
			]);

			return $this;
		}
	}
?>