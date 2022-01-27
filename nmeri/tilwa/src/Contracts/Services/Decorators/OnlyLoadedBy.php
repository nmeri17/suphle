<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface OnlyLoadedBy {

		public function allowedConsumers ():array;
	}
?>