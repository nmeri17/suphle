<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Config\{ Router as IRouter, ModuleFiles as IModuleFiles};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, ModuleFilesMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for tests that require a container. Boots and provides this container to them
	*/
	class IsolatedComponentTest extends TestCase {

		protected $container;

		protected function setUp ():void {

			$this->entityBindings();
		}

		protected function entityBindings ():self {

			$container = new Container;

			$container->provideSelf();

			foreach ($this->containerConfigs() as $contract => $className)

				$container->whenTypeAny()->needsAny([

					$contract => $container->getClass($className)
				]);

			$this->container = $container;

			return $this;
		}

		protected function containerConfigs ():array {

			return [

				IModuleFiles::class => ModuleFilesMock::class,

				IRouter::class => RouterMock::class
			];
		}
	}
?>