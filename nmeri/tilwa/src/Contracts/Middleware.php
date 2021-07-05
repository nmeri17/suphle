<?php
	
	namespace Tilwa\Contracts;

	interface Middleware {

		// return response/renderer
		public function process ($request, $requestHandler);
	}
?>