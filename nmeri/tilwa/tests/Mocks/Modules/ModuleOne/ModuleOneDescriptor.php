<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne;

	use Tilwa\App\ModuleDescriptor;

	use Tilwa\Contracts\Config\{ModuleFiles, Events, Router};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{ModuleFilesMock, EventsMock, RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	class ModuleOneDescriptor extends ModuleDescriptor {

		public function getConfigs():array {
			
			return parent::getConfigs() + [

				ModuleFiles::class => ModuleFilesMock::class,

				Events::class => EventsMock::class
			];
		}

		public function entityBindings ():self {

			$this->container->whenTypeAny()->needsAny([

				Router::class => new RouterMock(BrowserNoPrefix::class)
			]);

			return $this;
		}

		public function exports():object {

			return $this->container->getClass(ModuleApi::class);
		}

		public function exportsImplements():string {

			return ModuleOne::class;
		}
	}
?>