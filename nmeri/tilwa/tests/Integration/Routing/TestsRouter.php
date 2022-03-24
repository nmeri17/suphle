<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\{Config\Router as RouterContract, Presentation\BaseRenderer};

	use Tilwa\Testing\{TestTypes\IsolatedComponentTest, Condiments\DirectHttpTest};

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Config\RouterMock};

	class TestsRouter extends IsolatedComponentTest {

		use DirectHttpTest, CommonBinds {

			CommonBinds::concreteBinds as commonConcretes;
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