<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Hydration\Container;

	use ReflectionMethod;

	class CallbackDetails {

		public function __construct (

			protected readonly Container $container
		) {

			//
		}

		/**
		 * Autowires each callback level
		 * 
		 * @param {toBind} When present, callback will access its protected properties
		 * 
		 * @return mixed. Any value returned that's not a callback
		*/
		public function recursiveValueDerivation (callable $outerFunction, object $toBind = null) {

			$parameters = $this->container->getMethodParameters($outerFunction);

			if (!is_null($toBind))

				$outerFunction = $outerFunction->bindTo($toBind, $toBind /*access protected properties*/);

			$result = call_user_func_array($outerFunction, $parameters);

			while (is_callable($result))

				$result = $this->recursiveValueDerivation($result);

			return $result;
		}

		/**
		 * @return ReflectionAttribute[]
		*/
		public function getMethodAttributes (
			string $className, string $methodName,

			string $filterToAttribute = null
		):array {

			$attributesList = [];

			$inheritanceChain = array_filter(class_parents($className), function ($parent) use ($methodName) {

				return method_exists($parent, $methodName);
			});

			$inheritanceChain[] = $className;

			foreach ($inheritanceChain as $entry)

				$attributesList = array_merge(

					$attributesList, (new ReflectionMethod($entry, $methodName))

					->getAttributes($filterToAttribute)
				);

			return $attributesList;
		}
	}
?>