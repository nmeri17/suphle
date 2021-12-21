<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Config\{ Services as IServices, Laravel as ILaravel, Router as IRouter, Auth as IAuth, Transphporm as ITransphporm, ModuleFiles as IModuleFiles};

	use Tilwa\Config\{ Services, Laravel, Auth, Transphporm}; // using our default config for these

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, ModuleFilesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for tests that require a container. Boots and provides this container to them
	*/
	class IsolatedComponentTest extends TestCase {

		protected $container;

		protected function setUp ():void {

			$this->container = new Container;

			$this->bootContainer()->entityBindings();
		}

		protected function bootContainer ():self {

			$this->container->setConfigs($this->containerConfigs());

			return $this;
		}

		protected function entityBindings ():self {

			$this->container->whenTypeAny()->needsAny([

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