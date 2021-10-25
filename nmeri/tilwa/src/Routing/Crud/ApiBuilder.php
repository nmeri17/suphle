<?php
	namespace Tilwa\Routing\Crud;

	use Tilwa\Response\Format\Json;

	use Tilwa\Contracts\Routing\RouteCollection;

	class ApiBuilder extends BaseBuilder {

		private $allowedActions = [ "saveNew", "showAll", "showOne", "updateOne", "delete", "getSearchResults"];
		
		public function __construct(RouteCollection $collection) {

			$this->collection = $collection;
		}

		protected function saveNew():array {

			return $this->callParentWith( __FUNCTION__);
		}

		protected function showAll():array {

			return $this->callParentWith( __FUNCTION__);
		}

		protected function showOne():array {

			return $this->callParentWith( __FUNCTION__);
		}

		protected function updateOne():array {

			return $this->callParentWith( __FUNCTION__);
		}

		protected function deleteOne():array {

			return $this->callParentWith( __FUNCTION__);
		}

		protected function getSearchResults ():array {

			return $this->callParentWith( __FUNCTION__);
		}

		private function callParentWith (string $handler):array {

			$this->rendererMap[$handler] = new Json($handler);

			return parent::$handler();
		}
	}
?>