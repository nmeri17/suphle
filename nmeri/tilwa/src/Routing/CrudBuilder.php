<?php
	namespace Tilwa\Routing;

	use Tilwa\Response\Format\{Markup, Redirect, Reload, AbstractRenderer};

	class CrudBuilder {

		private $context, $viewPath, $resourceId,

		$allowedActions = ["showCreateForm", "saveNew", "showAll", "showOne", "updateOne", "delete"],

		$overwritable = [];
		
		function __construct(RouteCollection $context, string $viewPath, string $resourceId) {

			$this->context = $context;

			$this->viewPath = $viewPath . "/";

			$this->resourceId = $resourceId;
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

			$pattern = $this->resourceId;

			return compact("r", "pattern");
		}

		private function updateOne():array {

			$r = new Reload(__FUNCTION__);

			$r->setRouteMethod("put");

			$pattern = "EDIT_". $this->resourceId;

			return compact("r", "pattern");
		}

		private function deleteOne():array {

			$relativePath = $this->context->localPrefix . "/";

			$r = new Redirect(__FUNCTION__, function () use ($relativePath) {
				
				return "$relativePath";
			});

			$r->setRouteMethod("delete");

			$pattern = $this->resourceId;

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