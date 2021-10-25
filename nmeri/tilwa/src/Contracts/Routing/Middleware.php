<?php
	
	namespace Tilwa\Contracts\Routing;

	interface Middleware {

		// return response/renderer
		public function process ($request, $requestHandler);
	}
?>