<?php
	namespace _modules_shell\_module_name\Tests;

	use Suphle\Hydration\Container;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

	use _modules_shell\_module_name\Meta\_module_nameDescriptor;

	use _database_namespace\_resource_name;

	class _resource_nameHttpTest extends ModuleLevelTest {

		use BaseDatabasePopulator;

		protected function getModules ():array {

			return [new _module_nameDescriptor(new Container)];
		}

		protected function getActiveEntity ():string {

			return _resource_name::class;
		}
	}
?>