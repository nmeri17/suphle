<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFive;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ModuleFiles, Router};

	use Tilwa\Tests\Mocks\Modules\ModuleFive\Config\{ModuleFilesMock, RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleFive;

	class ModuleFiveDescriptor extends ModuleDescriptor {

		public function getConfigs():array {
			
			return parent::getConfigs() + [

				ModuleFiles::class => ModuleFilesMock::class,

				Router::class => RouterMock::class
			];
		}

		public function exports():object {

			return $this->container->getClass(ModuleApi::class);
		}

		public function exportsImplements():string {

			return ModuleFive::class;
		}
	}
?>