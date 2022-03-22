<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Tilwa\Hydration\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{Router, Events};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Config\{RouterMock, EventsMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleTwo;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				Router::class => RouterMock::class,

				Events::class => EventsMock::class
			]);
		}

		protected function simpleBinds ():string {

			return array_merge(parent::simpleBinds(), [

				ModuleTwo::class => ModuleApi::class
			]);
		}
	}
?>