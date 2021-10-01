<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ModuleFiles, Events};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Config\{ModuleFilesMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleTwo;

	class ModuleTwoDescriptor extends ModuleDescriptor {

		public function getConfigs():array {
			
			return parent::getConfigs() + [

				ModuleFiles::class => ModuleFilesMock::class
			];
		}

		public function entityBindings ():self {

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => new RouterMock(BrowserNoPrefix::class)
			]);

			return $this;
		}

		public function exports():object {

			return $this->container->getClass(ModuleApi::class);
		}

		public function exportsImplements():string {

			return ModuleTwo::class;
		}
	}
?>