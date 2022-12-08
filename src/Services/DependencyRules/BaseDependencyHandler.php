<?php
	namespace Suphle\Services\DependencyRules;

	use Suphle\Contracts\Server\DependencyFileHandler;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	abstract class BaseDependencyHandler implements DependencyFileHandler {

		protected array $argumentList;

		public function __construct (

			protected readonly Container $container,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function setRunArguments (array $argumentList):void {

			$this->argumentList = $argumentList;
		}

		/**
		 * @param {dependency} mixed. Can be any type passed as argument
		*/
		protected function isPermittedParent (array $parentList, $dependency):bool {

			$dependencyType = $this->objectMeta->getValueType($dependency);

			foreach ($parentList as $typeToMatch) {

				if (is_object($dependency)) {

					if ($this->objectMeta->stringInClassTree(

						$dependencyType, $typeToMatch
					))
					return true;
				}

				else if ($dependencyType == $typeToMatch) return true;
			}

			return false;
		}
	}
?>