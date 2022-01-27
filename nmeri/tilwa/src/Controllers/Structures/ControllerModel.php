<?php
	namespace Tilwa\Controllers\Structures;

	use Tilwa\Contracts\Database\Orm;

	abstract class ControllerModel {

		protected function onlyFields ():array {

			return ["id", "name"];
		}

		/**
		 * @return a query builder
		*/
		abstract protected function getBaseCriteria ();

		public function setDependencies (Orm $orm) {

			$this->orm = $orm;
		}

		/**
		 * This is the only method dev cares about
		*/
		public function getBuilder () {

			return $this->orm->selectFields($this->getBaseCriteria(), $this->onlyFields());
		}
	}
?>