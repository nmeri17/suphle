<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\Structures;

	use Tilwa\Bridge\Laravel\Config\BaseConfigLink;

	class FirstLevel extends BaseConfigLink {

		public function second_level ():SecondLevel {

			return new SecondLevel($this->nativeValues["second_level"]);
		}
	}
?>