<?php
	namespace Suphle\Services\Search;

	use Suphle\Services\UpdatelessService;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\{Database\OrmDialect, Services\Decorators\VariableDependencies};

	class SimpleSearch extends UpdatelessService implements VariableDependencies {

		protected $payloadStorage, $ormDialect;

		public function dependencyMethods ():array {

			return [

				"setPayloadStorage", "setOrmDialect"
			];
		}

		public function setPayloadStorage (PayloadStorage $payloadStorage):void {

			$this->payloadStorage = $payloadStorage;
		}

		public function setOrmDialect (OrmDialect $ormDialect):void {

			$this->ormDialect = $ormDialect;
		}

		public function convertToQuery ($baseModel, array $nonColumns) {

			foreach ($this->getConstraints($nonColumns) as $parameter => $value)

				if (method_exists($this, $parameter))

					$baseModel = $this->$parameter($baseModel, $value);

				else $baseModel = $this->ormDialect->addWhereClause($baseModel, [$parameter => $value]);

			return $baseModel;
		}

		/**
		 * @return only the query parameters we intend to search by
		*/
		protected function getConstraints (array $nonColumns):array {

			return $this->payloadStorage->except($nonColumns); // at the very least, omit query key since it's expected to be set in the [ModelfulPayload]
		}
	}
?>