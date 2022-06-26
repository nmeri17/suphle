<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Tilwa\Hydration\Structures\ObjectDetails;

	abstract class BaseArgumentModifier implements ModifiesArguments {

		protected $objectMeta;

		public function __construct ( ObjectDetails $objectMeta) {

			$this->objectMeta = $objectMeta;
		}

		/**
		 * {@inheritdoc}
		*/
		public function transformConstructor (object $dummyInstance, array $arguments):array {

			return $arguments;
		}

		/**
		 * {@inheritdoc}
		*/
		public function transformMethods (object $concreteInstance, array $arguments, string $methodName):array {

			return $arguments;
		}
	}
?>