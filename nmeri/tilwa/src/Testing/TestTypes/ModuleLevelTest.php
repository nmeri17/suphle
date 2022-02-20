<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Testing\Condiments\{ModuleReplicator, GagsException};

	use Tilwa\Modules\{ModulesBooter, ModuleDescriptor};

	use Tilwa\Events\ModuleLevelEvents;

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for testing components on a modular scale but that don't necessarily require interaction with the HTTP passage
	*/
	abstract class ModuleLevelTest extends TestCase {

		use ModuleReplicator, GagsException {

			GagsException::setUp as mufflerSetup;
		};

		protected $muffleExceptionBroadcast = true;

		protected function setUp ():void {

			$modules = $this->getModules();

			(new ModulesBooter($modules, new ModuleLevelEvents($modules)))
			->boot();

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
	}
?>