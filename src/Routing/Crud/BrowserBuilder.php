<?php
	namespace Suphle\Routing\Crud;

	use Suphle\Routing\MethodSorter;

	use Suphle\Response\Format\{Markup, Redirect, Reload};

	use Suphle\Contracts\{Routing\RouteCollection, Presentation\BaseRenderer};

	/**
	 * A list of renderer setting methods
	*/
	class BrowserBuilder extends BaseBuilder {

		private $viewPath, $viewModelPath;

		protected $validActions = [

			self::SHOW_CREATE, self::SAVE_NEW, self::SHOW_ALL,

			self::SHOW_ONE, self::UPDATE_ONE, self::DELETE_ONE,

			self::SHOW_SEARCH, self::SHOW_EDIT
		];
		
		public function __construct(RouteCollection $collection, string $viewPath, string $viewModelPath = null) {

			$this->collection = $collection;

			$this->viewPath = $viewPath . DIRECTORY_SEPARATOR;

			$this->viewModelPath = $viewModelPath ? $viewModelPath . DIRECTORY_SEPARATOR : $this->viewPath;
		}

		protected function showCreateForm ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "create-form");
		}

		protected function showEditForm ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "edit-form");
		}

		/**
		 * Redirect to "/resource/new_id"
		*/
		public function saveNew ():BaseRenderer {

			$prefix = $this->collection->_prefixCurrent();

			return new Redirect(__FUNCTION__, function () use ($prefix) {

				return function () use ($prefix) {
					return $prefix . "/" . $this->statusCode/*rawResponse["resource"]->id*/; // assumes the controller returns an array containing this key
				};
			});
		}

		protected function showAll ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "show-all");
		}

		protected function showOne ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "show-one");
		}

		protected function updateOne ():BaseRenderer {

			return new Reload(__FUNCTION__);
		}

		protected function deleteOne ():BaseRenderer {

			$prefix = $this->collection->_prefixCurrent();

			return new Redirect(__FUNCTION__, function () use ($prefix) {
				
				return "$prefix/";
			});
		}

		/**
		 * It's assumed that the same page where the form lives is where results will be displayed
		*/
		protected function showSearchForm ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "show-search-form");
		}

		private function getMarkupRenderer (string $handler, string $fileName):Markup {

			return new Markup(
				$handler, $this->viewPath . $fileName,

				$this->viewModelPath . $fileName
			);
		}
	}
?>