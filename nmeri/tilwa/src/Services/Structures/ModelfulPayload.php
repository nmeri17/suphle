<?php
	namespace Tilwa\Services\Structures;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Request\PayloadStorage;

	abstract class ModelfulPayload {

		protected $payloadStorage, $ormDialect;

		public function __construct (PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}

		protected function onlyFields ():array {

			return ["id", "name"];
		}

		/**
		 * @return a query builder after interacting with [payloadStorage]
		*/
		abstract protected function getBaseCriteria ();

		public function setDependencies (OrmDialect $ormDialect) {

			$this->ormDialect = $ormDialect;
		}

		/**
		 * This is the only method dev cares about
		*/
		public function getBuilder () {

			return $this->orm->selectFields($this->getBaseCriteria(), $this->onlyFields());
		}
	}
?>