<?php

	namespace Tilwa\Contracts;

	interface CanaryGateway {

		public function willLoad ():bool;

		public function entryClass ():string;
	}
?>