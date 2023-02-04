<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{Router, Events};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Config\{RouterMock, EventsMock};

	use Suphle\Tests\Mocks\Interactions\ModuleTwo;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				Router::class => RouterMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleTwo::class => ModuleApi::class,

				Events::class => EventsMock::class
			]);
		}
	}
?>