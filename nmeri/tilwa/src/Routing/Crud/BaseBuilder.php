<?php
	namespace Tilwa\Routing\Crud;

	use Tilwa\Contracts\Routing\CrudBuilder;

	use Exception;

	abstract class BaseBuilder implements CrudBuilder {

		private $idPlaceholder = "id";

		protected $overwritable = [], $allowedActions = [],

		$rendererMap = [], $collection;

		public function save():void {

			$createdRoutes = [];
			
			foreach ($this->allowedActions as $action) {

				["r" => $renderer, "pattern" => $pattern] = $this->$action();

				if (array_key_exists($action, $this->overwritable) )

					$renderer = $this->overwritable[$action];

				$createdRoutes[$pattern] = $renderer;
			}
			
			$this->collection->_setLastRegistered(

				$this->collection->_getMethodSorter()->descendingKeys($createdRoutes)
			);
		}

		protected function showCreateForm():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("get");

			$pattern = "CREATE";

			return compact("r", "pattern");
		}

		protected function showEditForm():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("get");

			$pattern = "EDIT_". $this->idPlaceholder; // safe to pass id in url for gets but mutative operations should come with a payload

			return compact("r", "pattern");
		}

		protected function saveNew():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("post");

			$pattern = "SAVE";

			return compact("r", "pattern");
		}

		protected function showAll():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("get");

			$pattern = "";

			return compact("r", "pattern");
		}

		protected function showOne():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("get");

			$pattern = $this->idPlaceholder;

			return compact("r", "pattern");
		}

		protected function updateOne():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("put");

			$pattern = "EDIT";

			return compact("r", "pattern");
		}

		protected function deleteOne():array {

			$r = $this->rendererMap[__FUNCTION__];

			$r->setRouteMethod("delete");

			$pattern = $this->idPlaceholder;

			return compact("r", "pattern");
		}

		private function registerSearchRoute (string $name):array {

			$renderer = $this->rendererMap[$name];

			$renderer->setRouteMethod("get");

			return ["r" => $renderer, "pattern" => "SEARCH"];
		}

		protected function showSearchForm ():array {

			return $this->registerSearchRoute(__FUNCTION__);
		}

		protected function getSearchResults ():array {

			return $this->registerSearchRoute(__FUNCTION__);
		}

		public function disableHandlers(array $handlers) {
			
			foreach ($handlers as $value) {

				$index = array_search($value, $this->allowedActions);

				unset($this->allowedActions[$index]);
			}
		}

		public function __call(string $method, $arguments):self {
			
			$this->overwritable[$this->getToReplace($method)] = current($arguments);

			return $this;
		}

		// convert `replaceSaveNew` to "saveNew"
		private function getToReplace(string $updating):?string {
			
			$internal = lcfirst(ltrim($updating, "replace"));

			if (in_array($internal, $this->allowedActions))
				
				return $internal;

			throw new Exception ("Unknown builder method '$updating'");
		}
	}
?>