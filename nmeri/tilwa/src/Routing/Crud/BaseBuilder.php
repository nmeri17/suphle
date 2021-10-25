<?php
	namespace Tilwa\Routing\Crud;

	abstract class BaseBuilder {

		private $idPlaceholder = "id";

		protected $overwritable = [], $allowedActions = [];

		public function save():array {

			$createdRoutes = [];
			
			foreach ($this->allowedActions as $action) {

				["r" => $renderer, "pattern" => $pattern] = $this->$action();

				if (array_key_exists($action, $this->overwritable) )

					$renderer = $this->overwritable[$action];

				$pattern = $this->resourceName . "_" . $pattern;

				$createdRoutes[$pattern] = $renderer;
			}
			return $createdRoutes; // instead of this, call something that'll trigger $this->lastRegistered on the collection
		}

		protected function showCreateForm():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "create-form");

			new Markup(__FUNCTION__, $this->viewPath . "create-form", $this->viewModelPath . "create-form"); // when vm path not found, use view own

			$r->setRouteMethod("get");

			$pattern = "CREATE";

			return compact("r", "pattern");
		}

		// @return Redirect to "/resource/new_id"
		protected function saveNew():array {

			$r = new Redirect(__FUNCTION__, function () {

				return $this->resourceName . "/" . $this->rawResponse["resource"]->id; // assumes the controller returns an array containing this key
			});

			$r->setRouteMethod("post");

			$pattern = "SAVE";

			return compact("r", "pattern");
		}

		protected function showAll():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-all");

			$r->setRouteMethod("get");

			$pattern = "";

			return compact("r", "pattern");
		}

		protected function showOne():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-one");

			$r->setRouteMethod("get");

			$pattern = $this->idPlaceholder;

			return compact("r", "pattern");
		}

		protected function updateOne():array {

			$r = new Reload(__FUNCTION__);

			$r->setRouteMethod("put");

			$pattern = "EDIT_". $this->idPlaceholder;

			return compact("r", "pattern");
		}

		protected function deleteOne():array {

			$r = new Redirect(__FUNCTION__, function () {
				
				return $this->context->_getLocalPrefix() . "/";
			});

			$r->setRouteMethod("delete");

			$pattern = $this->idPlaceholder;

			return compact("r", "pattern");
		}

		/**
		 * It's assumed to the same page where the form lives is where the results will be displayed
		*/
		protected function showSearchForm ():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-search-form");

			$r->setRouteMethod("get");

			$pattern = "SEARCH";

			return compact("r", "pattern");
		}

		public function disableHandlers(array $handlers) {
			
			foreach ($handlers as $value) {

				$index = array_search($value, $this->allowedActions);

				unset($this->allowedActions[$index]);
			}
		}

		public function __call(string $method, AbstractRenderer $renderer):self {
			
			$action = $this->getToReplace($method);

			if (!empty($action))

				$this->overwritable[$action] = $renderer;

			return $this;
		}

		// convert `replaceSaveNew` to "saveNew"
		private function getToReplace(string $updating):string {
			
			$internal = lcfirst(ltrim($updating, "replace"));

			if (array_key_exists($internal, $this->allowedActions))
				return $internal;
			return "";
		}
	}
?>