<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{ Router, Events, Laravel};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, EventsMock, LaravelMock};

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

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

				ModuleOne::class => ModuleApi::class
			]);
		}
	}
?>