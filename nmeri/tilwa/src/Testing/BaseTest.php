<?php
	namespace Tilwa\Testing;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter, Auth as IAuth, Transphporm as ITransphporm, ModuleFiles as IModuleFiles};

	use Tilwa\Contracts\Auth\UserHydrator as IUserHydrator;

	use Tilwa\Config\{ Services, Laravel, Auth, Transphporm}; // using our default config for these

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, ModuleFilesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use Tilwa\Tests\Mocks\Auth\ArrayUserHydratorMock;

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for tests that require a container. Boots and provides this container to them
	*/
	class BaseTest extends TestCase { // rename this to IsolatedComponentTest

		protected $container;

		protected function setUp ():void {

			$this->container = new Container;

			$this->bootContainer()->bindEntities();
		}

		protected function bootContainer ():self {

			$this->container->setConfigs($this->containerConfigs());

			return $this;
		}

		protected function bindEntities ():self {

			$this->container->whenTypeAny()->needsAny([

				IUserHydrator::class => new ArrayUserHydratorMock,

				Container::class => $this->container,

				IRouter::class => new RouterMock(BrowserNoPrefix::class)
			]);

			return $this;
		}

		protected function containerConfigs ():array {

			return [

				ILaravel::class => Laravel::class,

				IServices::class => Services::class,

				IAuth::class => Auth::class,

				ITransphporm::class => Transphporm::class,

				IModuleFiles::class => ModuleFilesMock::class
			];
		}
	}
?>