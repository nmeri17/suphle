<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{Router, Events, Flows};

	use Suphle\Tests\Mocks\Modules\ModuleThree\Config\{RouterMock, EventsMock, FlowMock};

	use Suphle\Tests\Mocks\Interactions\ModuleThree;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				Events::class => EventsMock::class,

				Flows::class => FlowMock::class,

				Router::class => RouterMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleThree::class => ModuleApi::class
			]);
		}
	}
?>