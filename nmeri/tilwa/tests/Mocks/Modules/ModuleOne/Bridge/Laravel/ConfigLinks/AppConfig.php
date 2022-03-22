<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks;

	use Tilwa\Bridge\Laravel\BaseConfigLink;

	class AppConfig extends BaseConfigLink {

		public function name ():string {

			return "Look, an override!";
		}
	}
?>