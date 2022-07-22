<?php

	namespace Suphle\Contracts\Routing;

	interface CanaryGateway {

		public function willLoad ():bool;

		public function entryClass ():string;
	}
?>