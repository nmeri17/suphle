<?php
	namespace Suphle\Contracts\Services\Decorators;

	/**
	 * Use on objects that expect to be modified on object A where they were injected, and these updates read on object B where they're equally injected--in other words, in cases where it only makes sense to have an app-wide instance, since the container creates a new one if it can't find an existing one for active context
	*/
	interface BindsAsSingleton {

		public function entityIdentity ():string;
	}
?>