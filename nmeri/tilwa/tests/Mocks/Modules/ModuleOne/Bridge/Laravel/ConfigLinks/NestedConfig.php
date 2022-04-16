<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks;

	use Tilwa\Bridge\Laravel\Config\BaseConfigLink;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\Structures\FirstLevel;

	class NestedConfig extends BaseConfigLink {

		public function first_level ():FirstLevel {

			return new FirstLevel($this->nativeValues["first_level"]);
		}
	}
?>