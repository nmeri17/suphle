<?php
	namespace Tilwa\Contracts\Routing;

	interface ApiRouteCollection {

		protected function _crudJson ():BaseBuilder;
	}
?>