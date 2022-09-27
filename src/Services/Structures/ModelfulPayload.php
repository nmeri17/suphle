<?php
	namespace Suphle\Services\Structures;

	use Suphle\Contracts\{Database\OrmDialect, Services\Decorators\VariableDependencies};

	use Suphle\Request\PayloadStorage;

	use Suphle\Routing\PathPlaceholders;

	abstract class ModelfulPayload implements VariableDependencies {

		protected $payloadStorage, $ormDialect, $pathPlaceholders;

		public function dependencyMethods ():array {

			return [

				"setPayloadStorage", "setPlaceholderStorage",

				"setOrmDialect"
			];
		}

		public function setPayloadStorage (PayloadStorage $payloadStorage):void {

			$this->payloadStorage = $payloadStorage;
		}

		public function setPlaceholderStorage (PathPlaceholders $pathPlaceholders):void {

			$this->pathPlaceholders = $pathPlaceholders;
		}

		public function setOrmDialect (OrmDialect $ormDialect):void {

			$this->ormDialect = $ormDialect;
		}

		protected function onlyFields ():array {

			return ["id", "name"];
		}

		/**
		 * @return a query builder after interacting with [payloadStorage]
		*/
		abstract protected function getBaseCriteria ():object;

		/**
		 * This is the only method caller cares about
		*/
		final public function getBuilder ():object {

			return $this->orm->selectFields(

				$this->getBaseCriteria(), $this->onlyFields()
			);
		}
	}
?>