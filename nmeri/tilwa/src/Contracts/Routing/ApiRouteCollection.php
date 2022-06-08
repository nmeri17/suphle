<?php
	namespace Tilwa\Contracts\Routing;

	interface ApiRouteCollection {

		public function _crudJson ():CrudBuilder;
	}
?>