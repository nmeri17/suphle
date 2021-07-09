<?php

	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter};

	use Tilwa\Config\{ Services, Laravel}; // using our default config for these

	use Tilwa\Tests\Mocks\Config\RouterMock;

	use PHPUnit\Framework\TestCase;

	class BaseTest extends TestCase {

		protected $container;

		protected function setUp ():void {

			$this->container = new Container; // for internal module testing, override this. Cycle through ModuleAssembly looking for which one prefixes our location

			$this->bootContainer();
		}

		protected function bootContainer ():void {

			$this->container->setConfigs($this->containerConfigs());
		}

		// when overriding this, call [bootContainer] as well
		protected function containerConfigs ():array {

			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IRouter::class => RouterMock::class
			];
		}

		protected function setHttpParams (string $requestPath, string $httpMethod) {

			$_GET["tilwa_path"] = $requestPath;

			$_SERVER["REQUEST_METHOD"] = $httpMethod ?? "get";
		}
	}
?>