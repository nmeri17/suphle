<?php
	namespace Suphle\Contracts\Services\Decorators;

	interface OnlyLoadedBy {

		public function allowedConsumers ():array;
	}
?>