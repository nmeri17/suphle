<?php
	namespace Suphle\Contracts\Auth;

	interface ColumnPayloadComparer {

		public function compare ():bool;

		public function getUser ():UserContract;
	}
?>