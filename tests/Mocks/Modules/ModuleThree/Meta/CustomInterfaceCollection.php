<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{Router, Events};

	use Suphle\Tests\Mocks\Modules\ModuleThree\Config\{RouterMock, EventsMock};

	use Suphle\Tests\Mocks\Interactions\ModuleThree;

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