<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Config;

	use Suphle\Contracts\Config\Events;

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>