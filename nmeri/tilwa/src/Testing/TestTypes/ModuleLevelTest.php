<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Modules\{ModulesBooter, ModuleDescriptor};

	use Tilwa\Hydration\Container;

	use Tilwa\Events\{ExecutionUnit, ModuleLevelEvents};

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for testing components on a modular scale but that don't necessarily require interaction with the HTTP passage
	*/
	abstract class ModuleLevelTest extends TestCase {

		protected function setUp ():void {

			$modules = $this->getModules();

			$bootStarter = new ModulesBooter($modules, new ModuleLevelEvents($modules));

			$bootStarter->boot();
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

		/**
		 * A blank container is given to the new module, with the assumption that we possibly wanna overwrite even the default objects (aside from only injecting absent configs)
		*/
		protected function replicateModule(string $descriptor, callable $customizer):ModuleDescriptor {

			$writer = new WriteOnlyContainer; // using unique instances rather than a fixed one so test can make multiple calls to clone modules

			$customizer($writer);

			return new $descriptor($writer->getContainer());
		}
	}
?>