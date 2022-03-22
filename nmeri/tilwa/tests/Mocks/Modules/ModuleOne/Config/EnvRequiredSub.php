<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\IO\Env\AbstractEnvLoader;

	class EnvRequiredSub extends AbstractEnvLoader {

		protected function validateFields ():void {

			$this->client->required(["DATABASE_NAME", "DATABASE_USER", "DATABASE_PASS"]);
		}
	}
?>