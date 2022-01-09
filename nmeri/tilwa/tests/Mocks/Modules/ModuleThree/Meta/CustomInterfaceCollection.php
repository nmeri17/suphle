<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Hydration\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{ModuleFiles, Router, Events};

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Config\{ModuleFilesMock, RouterMock, EventsMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				ModuleFiles::class => ModuleFilesMock::class,

				Router::class => RouterMock::class,

				Events::class => EventsMock::class
			]);
		}

		protected function simpleBinds ():string {

			return array_merge(parent::simpleBinds(), [

				ModuleThree::class => ModuleApi::class
			]);
		}
	}
?>