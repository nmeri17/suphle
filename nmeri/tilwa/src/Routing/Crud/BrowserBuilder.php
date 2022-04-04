<?php
	namespace Tilwa\Routing\Crud;

	use Tilwa\Response\Format\{Markup, Redirect, Reload, AbstractRenderer};

	use Tilwa\Contracts\Routing\RouteCollection;

	class BrowserBuilder {

		private $viewPath, $viewModelPath;

		protected $allowedActions = ["showCreateForm", "saveNew", "showAll", "showOne", "updateOne", "delete", "showSearchForm"];
		
		public function __construct(RouteCollection $collection, string $viewPath, string $viewModelPath = null) {

			$this->collection = $collection;

			$this->viewPath = $viewPath . DIRECTORY_SEPARATOR;

			$this->viewModelPath = $viewModelPath ? $viewModelPath . DIRECTORY_SEPARATOR : $this->viewPath;
		}

		protected function showCreateForm():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "create-form");
		}

		/**
		 * Redirect to "/resource/new_id"
		*/
		protected function saveNew():array {

			$handler = __FUNCTION__;

			return $this->callParentWith($handler, new Redirect($handler, function () {

				return $this->collection->_prefixCurrent() . "/" . $this->rawResponse["resource"]->id; // assumes the controller returns an array containing this key
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

		protected function deleteOne():array {

			$handler = __FUNCTION__;

			return $this->callParentWith($handler, new Redirect($handler, function () {
				
				return $this->collection->_prefixCurrent() . "/";
			}));
		}

		/**
		 * It's assumed to the same page where the form lives is where the results will be displayed
		*/
		protected function showSearchForm ():array {

			return $this->registerMarkupRenderer(__FUNCTION__, "show-search-form");
		}

		private function callParentWith (string $handler, AbstractRenderer $renderer):array {

			$this->rendererMap[$handler] = $renderer;

			return parent::$handler();
		}

		private function registerMarkupRenderer (string $handler, string $fileName):array {

			$this->rendererMap[$handler] = new Markup($handler, $this->viewPath . $fileName, $this->viewModelPath . $fileName);

			return parent::$handler();
		}
	}
?>