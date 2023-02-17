<?php
	namespace Suphle\Tests\Integration\Routing;

	use Suphle\Routing\RouteManager;

	use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer};

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\BrowserNoPrefix, Meta\ModuleOneDescriptor, Config\RouterMock};

	class TestsRouter extends ModuleLevelTest {

		protected function getEntryCollection ():string {

			return BrowserNoPrefix::class;
		}

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection()
					]);
				})
			];
		}

		protected function fakeRequest (string $url, string $httpMethod = "get", array $payload = null):?BaseRenderer {

			if (is_null($payload)) $this->$httpMethod($url);

			else $this->$httpMethod($url, $payload);

			$router = $this->getContainer()->getClass(RouteManager::class);

			$router->findRenderer();

			return $router->getActiveRenderer();
		}
	}
?>