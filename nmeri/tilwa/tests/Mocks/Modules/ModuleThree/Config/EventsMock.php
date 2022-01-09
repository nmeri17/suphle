<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Config;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>