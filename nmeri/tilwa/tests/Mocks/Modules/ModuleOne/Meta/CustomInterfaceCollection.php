<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Hydration\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{ModuleFiles, Router, Events, Laravel};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{ModuleFilesMock, RouterMock, EventsMock, LaravelMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				ModuleFiles::class => ModuleFilesMock::class,

				Events::class => EventsMock::class,

				Router::class => RouterMock::class,

				Laravel::class => LaravelMock::class
			]);
		}

		protected function simpleBinds ():string {

			return array_merge(parent::simpleBinds(), [

				ModuleOne::class => ModuleApi::class
			]);
		}
	}
?>