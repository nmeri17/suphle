<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Config;

	use Suphle\Contracts\Config\Events;

	use Suphle\Tests\Mocks\Modules\ModuleThree\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>