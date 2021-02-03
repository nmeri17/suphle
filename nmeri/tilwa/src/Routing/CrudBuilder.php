<?php
	namespace Tilwa\Routing;

	use Tilwa\Http\Response\Format\{Markup, Redirect, Reload, AbstractRenderer};

	class CrudBuilder {

		private $context;

		private $viewPath;

		private $allowedActions;

		private $overwritable;
		
		function __construct(RouteCollection $context, string $viewPath) {

			$this->context = $context;

			$this->viewPath = $viewPath . "/";

			$this->allowedActions = ["showCreateForm", "saveNew", "showAll", "showOne", "updateOne", "delete"];

			$this->overwritable = [];
		}

		public function save():array {

			$createdRoutes = [];
			
			foreach ($this->allowedActions as $action) {

				$definition = $this->$action();

				if (array_key_exists($action, $this->overwritable) )

					$definition["r"] = $this->overwritable[$action];

				$createdRoutes[$definition["pattern"]] = $definition["r"];
			}
			return $createdRoutes;
		}

		private function showCreateForm():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "create-form");

			$r->routeMethod = "get";

			$pattern = "CREATE";

			return compact("r", "pattern");
		}

		// @return Redirect to "/resource/new_id"
		private function saveNew():array {

			$relativePath = $this->context->localPrefix . "/";

			$r = new Redirect(__FUNCTION__, function () use ($relativePath) {
				
				return $relativePath . $this->rawResponse["resource"]->id; // assumes the controller returns an array containing this key
			});

			$r->routeMethod = "post";

			$pattern = "SAVE";

			return compact("r", "pattern");
		}

		private function showAll():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-all");

			$r->routeMethod = "get";

			$pattern = "";

			return compact("r", "pattern");
		}

		private function showOne():array {

			$r = new Markup(__FUNCTION__, $this->viewPath . "show-one");

			$r->routeMethod = "get";

			$pattern = "resourceId"; // request objects for crud routes must implement this key

			return compact("r", "pattern");
		}

		private function updateOne():array {

			$r = new Reload(__FUNCTION__);

			$r->routeMethod = "put";

			$pattern = "EDIT_resourceId";

			return compact("r", "pattern");
		}

		private function deleteOne():array {

			$relativePath = $this->context->localPrefix . "/";

			$r = new Redirect(__FUNCTION__, function () use ($relativePath) {
				
				return "$relativePath";
			});

			$r->routeMethod = "delete";

			$pattern = "resourceId";

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