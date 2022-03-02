<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Testing\Condiments\{ModuleReplicator, GagsException};

	use Tilwa\Testing\Proxies\Extensions\{FrontDoor, MiddlewareManipulator};

	use Tilwa\Testing\Proxies\ModuleHttpTest;

	use Tilwa\Modules\{ ModuleDescriptor, ModuleToRoute};

	use Tilwa\Hydration\Container; 

	use Tilwa\Middleware\MiddlewareRegistry;

	use PHPUnit\Framework\TestCase;

	use Illuminate\Testing\TestResponse;

	abstract class ModuleLevelTest extends TestCase {

		use ModuleReplicator, GagsException, ModuleHttpTest {

			GagsException::setUp as mufflerSetup;
		};

		protected $muffleExceptionBroadcast = true, $entrance;

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor($this->getModules());

			$entrance->bootModules();

			$entrance->extractFromContainer();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules():array;

		protected function getModuleFor (string $interface) {

			foreach ($this->getModules() as $descriptor)

				if ($interface == $descriptor->exportsImplements()) {

					$descriptor->warmUp();

					$descriptor->prepareToRun();

					return $descriptor->materialize();
				}
		}

		protected function massProvide (array $provisions):void {

			foreach ($this->getModules() as $descriptor)

				$descriptor->getContainer()->whenTypeAny()

				->needsAny($provisions);
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}
	}
?>