<?php
	namespace Tilwa\Routing\Crud;

	use Tilwa\Contracts\{Routing\CrudBuilder, Presentation\BaseRenderer};

	use Exception;

	abstract class BaseBuilder implements CrudBuilder {

		const SHOW_CREATE = "showCreateForm", SAVE_NEW = "saveNew",

		SHOW_ALL = "showAll", SHOW_ONE = "showOne",

		UPDATE_ONE = "updateOne", DELETE_ONE = "deleteOne",

		SHOW_SEARCH = "showSearchForm", SHOW_EDIT = "showEditForm",

		SEARCH_RESULTS = "getSearchResults";

		private $idPlaceholder = "id";

		protected $overwritable = [], $rendererMap = [],

		$disabledHandlers = [], $validActions = [], $collection;

		public function registerCruds ():void {

			$createdRoutes = [];
			
			foreach ($this->findActiveRenderers() as $getRenderer => $rendererDetails) {

				if (array_key_exists($getRenderer, $this->overwritable) )

					$renderer = $this->overwritable[$getRenderer];

				else $renderer = $this->$getRenderer();

				$pattern = $this->$rendererDetails($renderer);

				$createdRoutes[$pattern] = $renderer;
			}
			
			$this->collection->_setLastRegistered(

				$this->collection->_getMethodSorter()->descendingKeys($createdRoutes)
			);
		}

		protected function findActiveRenderers ():array {

			return array_filter($this->allModifiers(), function ($action) {

				return in_array($action, $this->validActions) &&

				!in_array($action, $this->disabledHandlers);
			}, ARRAY_FILTER_USE_KEY);
		}

		private function allModifiers ():array {

			return [

				self::SHOW_CREATE => "showCreateModifier",

				self::SAVE_NEW => "saveNewModifier",

				self::SHOW_ALL => "showAllModifier",

				self::SHOW_ONE => "showOneModifier",

				self::UPDATE_ONE => "updateOneModifier",

				self::DELETE_ONE => "deleteOneModifier",

				self::SHOW_SEARCH => "showSearchModifier",

				self::SHOW_EDIT => "showEditModifier",

				self::SEARCH_RESULTS => "searchResultsModifier"
			];
		}

		public function disableHandlers(array $handlers):void {

			$this->disabledHandlers = $handlers;
		}

		public function replaceRenderer (string $setterName, BaseRenderer $renderer):self {

			if (!array_key_exists($setterName, $this->allModifiers()))
				
				throw new Exception ("Unknown renderer setter: '$setterName'");
			
			$this->overwritable[$setterName] = $renderer;

			return $this;
		}

		protected function showCreateModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("get");

			return "CREATE";
		}

		protected function showEditModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("get");

			return "EDIT_". $this->idPlaceholder; // safe to pass id in url for gets but mutative operations should come with a payload
		}

		protected function saveNewModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("post");

			return "SAVE";
		}

		protected function showAllModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("get");

			return "_index";
		}

		protected function showOneModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("get");

			return $this->idPlaceholder;
		}

		protected function updateOneModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("put");

			return "EDIT";
		}

		protected function deleteOneModifier (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("delete");

			return "DELETE";
		}

		private function registerSearchRoute (BaseRenderer $renderer):string {

			$renderer->setRouteMethod("get");

			return "SEARCH";
		}

		protected function showSearchModifier (BaseRenderer $renderer):string {

			return $this->registerSearchRoute($renderer);
		}

		protected function searchResultsModifier (BaseRenderer $renderer):string {

			return $this->registerSearchRoute($renderer);
		}
	}
?>