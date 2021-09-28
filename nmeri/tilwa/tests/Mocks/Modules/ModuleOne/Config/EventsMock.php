<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>