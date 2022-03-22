<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\IO\Env\AbstractEnvLoader;

	class EnvRequiredSub extends AbstractEnvLoader {

		protected function validateFields ():void {

			$this->client->required(["DB_NAME", "DB_USERNAME", "DB_PASS"]);
		}
	}
?>