<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Hydration\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{ModuleFiles, Router};

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Config\{ModuleFilesMock, RouterMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				ModuleFiles::class => ModuleFilesMock::class,

				Router::class => RouterMock::class
			]);
		}

		protected function simpleBinds ():string {

			return array_merge(parent::simpleBinds(), [

				ModuleThree::class => ModuleApi::class
			]);
		}
	}
?>