<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Hydration\Structures\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{Router, Events};

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Config\{RouterMock, EventsMock};

	use Tilwa\Tests\Mocks\Interactions\ModuleThree;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				Router::class => RouterMock::class,

				Events::class => EventsMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleThree::class => ModuleApi::class
			]);
		}
	}
?>