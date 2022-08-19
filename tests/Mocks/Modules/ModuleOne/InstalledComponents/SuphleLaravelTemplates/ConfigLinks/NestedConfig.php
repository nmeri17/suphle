<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks;

	use Suphle\Bridge\Laravel\Config\BaseConfigLink;

	use Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks\Structures\FirstLevel;

	class NestedConfig extends BaseConfigLink {

		public function first_level ():FirstLevel {

			return new FirstLevel($this->nativeValues["first_level"]);
		}
	}
?>