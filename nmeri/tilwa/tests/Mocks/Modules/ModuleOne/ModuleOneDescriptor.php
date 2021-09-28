<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ModuleFiles, Events};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{ModuleFilesMock, EventsMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class ModuleOneDescriptor extends ModuleDescriptor {

		public function getConfigs():array {
			
			return parent::getConfigs() + [

				ModuleFiles::class => ModuleFilesMock::class,

				Events::class => EventsMock::class
			];
		}

		public function exports():object {

			return $this->container->getClass(ModuleApi::class);
		}

		public function exportsImplements():string {

			return ModuleOne::class;
		}
	}
?>