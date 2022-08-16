<?php
	namespace Suphle\Tests\Mocks\Modules\_module_name\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{ModuleFiles, Router};

	use Suphle\Tests\Mocks\Modules\_module_name\Config\{ModuleFilesMock, RouterMock};

	use ModulSuphle\Tests\Mocks\Interactions\_module_name;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				ModuleFiles::class => ModuleFilesMock::class,

				Router::class => RouterMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				_module_name::class => ModuleApi::class
			]);
		}
	}
?>