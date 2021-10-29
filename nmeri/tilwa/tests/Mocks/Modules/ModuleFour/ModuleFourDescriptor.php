<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFour;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ModuleFiles, Router};

	use Tilwa\Tests\Mocks\Modules\ModuleFour\Config\{ModuleFilesMock, RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleFour;

	class ModuleFourDescriptor extends ModuleDescriptor {

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

			return ModuleFour::class;
		}
	}
?>