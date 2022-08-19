<?php
	namespace Suphle\Routing\Crud;

	use Suphle\Response\Format\Json;

	use Suphle\Contracts\{Routing\RouteCollection, Presentation\BaseRenderer};

	class ApiBuilder extends BaseBuilder {

		protected $validActions = [

			self::SAVE_NEW, self::SHOW_ALL,

			self::SHOW_ONE, self::UPDATE_ONE, self::DELETE_ONE,

			self::SEARCH_RESULTS
		];
		
		public function __construct(RouteCollection $collection) {

			$this->collection = $collection;
		}

		protected function saveNew():BaseRenderer {

			return new Json(__FUNCTION__);
		}

		protected function showAll():BaseRenderer {

			return new Json(__FUNCTION__);
		}

		protected function showOne():BaseRenderer {

			return new Json(__FUNCTION__);
		}

		protected function updateOne():BaseRenderer {

			return new Json(__FUNCTION__);
		}

		protected function deleteOne():BaseRenderer {

			return new Json(__FUNCTION__);
		}

		protected function getSearchResults ():BaseRenderer {

			return new Json(__FUNCTION__);
		}
	}
?>