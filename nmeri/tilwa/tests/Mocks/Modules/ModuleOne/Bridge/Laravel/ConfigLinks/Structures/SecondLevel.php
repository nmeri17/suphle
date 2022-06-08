<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\Structures;

	use Tilwa\Bridge\Laravel\Config\BaseConfigLink;

	class SecondLevel extends BaseConfigLink {

		public function name ():string {

			return "Look, an override!";
		}

		public function value ():int {

			return $this->nativeValues["value"];
		}
	}
?>