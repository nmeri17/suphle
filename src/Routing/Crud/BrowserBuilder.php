<?php
	namespace Suphle\Routing\Crud;

	use Suphle\Routing\MethodSorter;

	use Suphle\Response\Format\{Markup, Redirect, Reload};

	use Suphle\Contracts\{Routing\RouteCollection, Presentation\BaseRenderer};

	/**
	 * A list of renderer setting methods
	*/
	class BrowserBuilder extends BaseBuilder {

		public const SAVE_NEW_KEY = "resource";

		private string $markupPath, // relative markup path for this resource. The absolute path is derived by htmlParser

		$templatePath;

		protected array $validActions = [

			self::SHOW_CREATE, self::SAVE_NEW, self::SHOW_ALL,

			self::SHOW_ONE, self::UPDATE_ONE, self::DELETE_ONE,

			self::SHOW_SEARCH, self::SHOW_EDIT
		];
		
		public function __construct(RouteCollection $collection, string $markupPath, string $templatePath = null) {

			$this->collection = $collection;

			$this->markupPath = $markupPath . DIRECTORY_SEPARATOR;

			$this->templatePath = $templatePath ? $templatePath . DIRECTORY_SEPARATOR : $this->markupPath;
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

			$prefix = rtrim($this->markupPath, DIRECTORY_SEPARATOR);

			return new Redirect(__FUNCTION__, fn() => function () use ($prefix) {
					return "/$prefix/" .

					$this->rawResponse[BrowserBuilder::SAVE_NEW_KEY]->id; // assumes the controller returns an array containing this key
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

			return new Redirect(__FUNCTION__, fn() => "$prefix/");
		}

		/**
		 * It's assumed that the same page where the form lives is where results will be displayed
		*/
		protected function showSearchForm ():BaseRenderer {

			return $this->getMarkupRenderer(__FUNCTION__, "show-search-form");
		}

		private function getMarkupRenderer (string $handler, string $fileName):Markup {

			return new Markup(
				$handler, $this->markupPath . $fileName,

				$this->templatePath . $fileName
			);
		}
	}
?>