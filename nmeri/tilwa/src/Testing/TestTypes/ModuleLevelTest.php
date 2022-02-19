<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Testing\Condiments\ModuleReplicator;

	use Tilwa\Modules\{ModulesBooter, ModuleDescriptor};

	use Tilwa\Events\ModuleLevelEvents;

	use PHPUnit\Framework\TestCase;

	/**
	 * Used for testing components on a modular scale but that don't necessarily require interaction with the HTTP passage
	*/
	abstract class ModuleLevelTest extends TestCase {

		use ModuleReplicator;

		protected function setUp ():void {

			$modules = $this->getModules();

			(new ModulesBooter($modules, new ModuleLevelEvents($modules)))
			->boot();
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
	}
?>