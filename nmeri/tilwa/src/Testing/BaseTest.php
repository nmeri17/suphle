<?php

	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter, Auth as IAuth};

	use Tilwa\Contracts\UserHydrator as IUserHydrator;

	use Tilwa\Config\{ Services, Laravel, Auth}; // using our default config for these

	use Tilwa\Tests\Mocks\Config\RouterMock;

	use Tilwa\Tests\Mocks\Auth\ArrayUserHydratorMock;

	use PHPUnit\Framework\TestCase;

	class BaseTest extends TestCase {

		protected $container;

		protected function setUp ():void {

			$this->container = new Container; // for internal module testing, override this. Cycle through ModuleAssembly looking for which one prefixes our location

			$this->bootContainer()->bindEntities();
		}

		protected function bootContainer ():self {

			$this->container->setConfigs($this->containerConfigs());

			return $this;
		}

		protected function bindEntities ():self {

			$this->container->whenTypeAny()

			->needsAny([

				IUserHydrator::class => new ArrayUserHydratorMock
			]);

			return $this;
		}

		// when overriding this, call [bootContainer] as well
		protected function containerConfigs ():array {

			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IRouter::class => RouterMock::class,

				IAuth::class => Auth::class
			];
		}

		protected function setHttpParams (string $requestPath, string $httpMethod = null) {

			$_GET["tilwa_path"] = $requestPath;

			$_SERVER["REQUEST_METHOD"] = $httpMethod ?? "get";
		}
	}
?>