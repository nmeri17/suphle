<?php

	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use Tilwa\Contracts\Config\Router as IRouter;

	class BaseRouterTest extends BaseTest {

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