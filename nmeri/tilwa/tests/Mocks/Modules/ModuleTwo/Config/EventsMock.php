<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Config;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>