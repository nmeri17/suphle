<?php
	namespace AllModules\_module_name\Meta;

	use Suphle\Hydration\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{ModuleFiles, Router};

	use AllModules\_module_name\Config\{ModuleFilesMock, RouterMock};

	use ModuleInteractions\_module_name;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {
			
			return array_merge(parent::getConfigs(), [

				ModuleFiles::class => ModuleFilesMock::class,

				Router::class => RouterMock::class
			]);
		}

		protected function simpleBinds ():string {

			return array_merge(parent::simpleBinds(), [

				_module_name::class => ModuleApi::class
			]);
		}
	}
?>