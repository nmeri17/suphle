<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\GagsException;

	use Tilwa\Contracts\Config\{ Router as IRouter, ModuleFiles as IModuleFiles};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, ModuleFilesMock};

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for tests that require a container. Boots and provides this container to them
	*/
	class IsolatedComponentTest extends TestCase {

		use GagsException {

			GagsException::setUp as mufflerSetup;
		}

		protected $container,

		$muffleExceptionBroadcast = true;

		protected function setUp ():void {

			$this->entityBindings();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();
		}

		protected function entityBindings ():void {

			$this->container = $container = new Container;

			$container->provideSelf();

			foreach ($this->containerConfigs() as $contract => $className)

				$container->whenTypeAny()->needsAny([

					$contract => $container->getClass($className)
				]);
		}

		protected function containerConfigs ():array {

			return [

				IModuleFiles::class => ModuleFilesMock::class,

				IRouter::class => RouterMock::class
			];
		}

		// used for normalizing traits that are applicable to both this and module level test
		protected function getContainer ():Container {

			return $this->container;
		}

		protected function massProvide (array $provisions):void {

			$this->container->whenTypeAny()->needsAny($provisions);
		}
	}
?>