<?php
	namespace Suphle\Services\Structures;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Request\PayloadStorage;

	use Suphle\Routing\PathPlaceholders;

	abstract class ModelfulPayload {

		protected $payloadStorage, $ormDialect, $pathPlaceholders;

		public function __construct (PayloadStorage $payloadStorage, PathPlaceholders $pathPlaceholders, OrmDialect $ormDialect) {

			$this->payloadStorage = $payloadStorage;

			$this->pathPlaceholders = $pathPlaceholders;

			$this->ormDialect = $ormDialect;
		}

		protected function onlyFields ():array {

			return ["id", "name"];
		}

		/**
		 * @return a query builder after interacting with [payloadStorage]
		*/
		abstract protected function getBaseCriteria ();

		/**
		 * This is the only method caller cares about
		*/
		public function getBuilder () {

			return $this->orm->selectFields($this->getBaseCriteria(), $this->onlyFields());
		}
	}
?>