<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{ Router, Events, Laravel, Flows};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, EventsMock, LaravelMock, FlowMock};

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {

			return array_merge(parent::getConfigs(), [

				Flows::class => FlowMock::class,

				Laravel::class => LaravelMock::class,

				Router::class => RouterMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleOne::class => ModuleApi::class,

				Events::class => EventsMock::class
			]);
		}
	}
?>