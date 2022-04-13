<?php
	namespace Tilwa\Routing\Crud;

	use Tilwa\Routing\MethodSorter;

	use Tilwa\Response\Format\{Markup, Redirect, Reload};

	use Tilwa\Contracts\{Routing\RouteCollection, Presentation\BaseRenderer};

	class BrowserBuilder extends BaseBuilder {

		private $viewPath, $viewModelPath;

		protected $allowedActions = ["showCreateForm", "saveNew", "showAll", "showOne", "updateOne", "deleteOne", "showSearchForm", "showEditForm"];
		
		public function __construct(RouteCollection $collection, string $viewPath, string $viewModelPath = null) {

			$this->collection = $collection;

			$this->viewPath = $viewPath . DIRECTORY_SEPARATOR;

			$this->viewModelPath = $viewModelPath ? $viewModelPath . DIRECTORY_SEPARATOR : $this->viewPath;
		}

		protected function showCreateForm():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "create-form");
		}

		protected function showEditForm ():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "edit-form");
		}

		/**
		 * Redirect to "/resource/new_id"
		*/
		public function saveNew ():array {

			$handler = __FUNCTION__;

			$prefix = $this->collection->_prefixCurrent();

			return $this->callParentWith($handler, new Redirect($handler, function () use ($prefix) {

				return function () use ($prefix) {
					return $prefix . "/" . $this->statusCode/*rawResponse["resource"]->id*/; // assumes the controller returns an array containing this key
				};
			}));
		}

		protected function showAll():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "show-all");
		}

		protected function showOne():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "show-one");
		}

		protected function updateOne():array {

			$handler = __FUNCTION__;

			return $this->callParentWith($handler, new Reload($handler));
		}

		protected function deleteOne ():array {

			$handler = __FUNCTION__;

			$prefix = $this->collection->_prefixCurrent();

			return $this->callParentWith($handler, new Redirect($handler, function () use ($prefix) {
				
				return "$prefix/";
			}));
		}

		/**
		 * It's assumed to the same page where the form lives is where the results will be displayed
		*/
		protected function showSearchForm ():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "show-search-form");
		}

		private function callParentWith (string $handler, BaseRenderer $renderer):array {

			$this->rendererMap[$handler] = $renderer;

			return parent::$handler();
		}

		private function registerMarkupRenderer (string $handler, string $fileName):array {

			$this->rendererMap[$handler] = new Markup($handler, $this->viewPath . $fileName, $this->viewModelPath . $fileName);

			return parent::$handler();
		}
	}
?>