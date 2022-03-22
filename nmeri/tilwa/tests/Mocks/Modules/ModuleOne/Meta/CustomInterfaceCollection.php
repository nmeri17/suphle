<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Hydration\Structures\BaseInterfaceCollection;

	use Tilwa\Contracts\Config\{ Router, Events, Laravel};

	use Tilwa\Contracts\IO\EnvAccessor;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, EventsMock, LaravelMock, EnvRequiredSub};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				Events::class => EventsMock::class,

				Router::class => RouterMock::class,

				Laravel::class => LaravelMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleOne::class => ModuleApi::class,

				EnvAccessor::class => EnvRequiredSub::class
			]);
		}
	}
?>