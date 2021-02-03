<?php
	namespace Tilwa\Routing;

	class CrudBuilder {
		
		function __construct(RouteCollection $context) {
			// pull what controller class is active

			$resourceTemplates = []; // showCreateForm, saveNew, showAll, showOne, update, delete

			// foreach ($resourceTemplates)
		}

		public function save():array {
			
			foreach ($this->createdRoutes as $route)

				$this->context->_register($route);

			return $this->createdRoutes; // is expected to contain a bunch of renderers
		}
	}
?>