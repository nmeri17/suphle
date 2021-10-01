<?php
	namespace Tilwa\Routing;

	use Tilwa\Response\Format\{Markup, Redirect, Reload, AbstractRenderer};

	class CrudBuilder {

		private $context, $viewPath, $idPlaceholder = "id",

		$allowedActions = ["showCreateForm", "saveNew", "showAll", "showOne", "updateOne", "delete"],

		$overwritable = [];
		
		function __construct(RouteCollection $context, string $viewPath) {

			$this->context = $context;

			$this->viewPath = $viewPath . "/";
		}

		public function save():array {

			$createdRoutes = [];
			
			foreach ($this->allowedActions as $action) {

				["r" => $renderer, "pattern" => $pattern] = $this->$action();

				if (array_key_exists($action, $this->overwritable) )

					$renderer = $this->overwritable[$action];

				$createdRoutes[$pattern] = $renderer;
			}
			return $createdRoutes;
		}

		private function showCreateForm():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "create-form");

			$r->setRouteMethod("get");

			$pattern = "CREATE";

			return compact("r", "pattern");
		}

		// @return Redirect to "/resource/new_id"
		private function saveNew():array {

			$relativePath = $this->context->localPrefix . "/";

			$r = new Redirect(__FUNCTION__, function () use ($relativePath) {
				
				return $relativePath . $this->rawResponse["resource"]->id; // assumes the controller returns an array containing this key
			});

			$r->setRouteMethod("post");

			$pattern = "SAVE";

			return compact("r", "pattern");
		}

		private function showAll():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-all");

			$r->setRouteMethod("get");

			$pattern = "";

			return compact("r", "pattern");
		}

		private function showOne():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-one");

			$r->setRouteMethod("get");

			$pattern = $this->idPlaceholder;

			return compact("r", "pattern");
		}

		private function updateOne():array {

			$r = new Reload(__FUNCTION__);

			$r->setRouteMethod("put");

			$pattern = "EDIT_". $this->idPlaceholder;

			return compact("r", "pattern");
		}

		private function deleteOne():array {

			$relativePath = $this->context->_getLocalPrefix() . "/";

			$r = new Redirect(__FUNCTION__, function () use ($relativePath) {
				
				return "$relativePath";
			});

			$r->setRouteMethod("delete");

			$pattern = $this->idPlaceholder;

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