<?php
	namespace Tilwa\Services\Search;

	use Tilwa\Services\UpdatelessService;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Contracts\Database\Orm;

	class SimpleSearch extends UpdatelessService {

		protected $payloadStorage, $orm;

		public function __construct (PayloadStorage $payloadStorage, Orm $orm) {

			$this->payloadStorage = $payloadStorage;

			$this->orm = $orm;
		}

		public function convertToQuery ($baseModel, string $queryField) {

			foreach ($this->getConstraints($queryField) as $parameter => $value)

				if (method_exists($this, $parameter))

					$baseModel = $this->$parameter($baseModel, $value);

				else $baseModel = $this->orm->addWhereClause($baseModel, [$parameter => $value]);

			return $baseModel;
		}

		/**
		 * @return only the query parameters we intend to search by
		*/
		protected function getConstraints (string $queryField):array {

			return $this->payloadStorage->except([$queryField]); // omitting since it's expected to be set in the [ModelfulPayload]
		}
	}
?>