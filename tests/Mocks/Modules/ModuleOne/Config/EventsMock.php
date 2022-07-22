<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

	use Suphle\Contracts\Config\Events;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Events\AssignListeners;

	class EventsMock implements Events {

		public function getManager ():string {

			return AssignListeners::class;
		}
	}
?>