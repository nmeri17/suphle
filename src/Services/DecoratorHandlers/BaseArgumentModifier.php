<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifiesArguments;

	use Suphle\Hydration\Structures\ObjectDetails;

	abstract class BaseArgumentModifier implements ModifiesArguments {

		public function __construct(protected ObjectDetails $objectMeta)
  {
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