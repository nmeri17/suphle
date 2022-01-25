<?php
	namespace Tilwa\Contracts\Services;

	interface OnlyLoadedBy {// base http impl this guy, its handler => modifInj, receives the caller

		public function allowedConsumers ():array;
	}
?>