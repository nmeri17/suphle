<?php
	namespace Suphle\Hydration\Structures;

	trait BaseSingletonBind {

		public function entityIdentity ():string {

			return get_called_class();
		}
	}
?>