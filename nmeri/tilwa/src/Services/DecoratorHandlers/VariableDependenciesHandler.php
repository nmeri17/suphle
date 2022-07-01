<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Tilwa\Hydration\{Container, Structures\ObjectDetails};

	class VariableDependenciesHandler implements ModifyInjected {

		private $container;

		public function __construct (Container $container, ObjectDetails $objectMeta) {

			$this->container = $container;

			parent::__construct($objectMeta);
		}

		/**
		 * @param {concrete} VariableDependencies
		*/
		public function examineInstance (object $concrete, string $caller):object {

			foreach ($concrete->dependencyNames() as $property => $dependency) {

				/**
				 * Since class/consumer isn't directly communicating with container, provisions to the specific class won't work
				 * 
				 * We're not using reflection, to signify lack of support for private properties discourage classes that can't exist without this handler
				*/
				$concrete->$property = $this->container->getClass($dependency);
			}

			return $concrete;
		}
	}
?>