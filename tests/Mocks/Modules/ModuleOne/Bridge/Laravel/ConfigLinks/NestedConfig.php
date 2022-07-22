<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks;

	use Suphle\Bridge\Laravel\Config\BaseConfigLink;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\ConfigLinks\Structures\FirstLevel;

	class NestedConfig extends BaseConfigLink {

		public function first_level ():FirstLevel {

			return new FirstLevel($this->nativeValues["first_level"]);
		}
	}
?>