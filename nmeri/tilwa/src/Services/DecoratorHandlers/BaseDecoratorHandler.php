<?
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Hydration\{Container, ScopeHandlers\ModifyInjected};

	abstract class BaseDecoratorHandler implements ModifyInjected {

		protected $originAccessor;

		public function __construct (OriginAccessor $originAccessor) {

			$this->originAccessor = $originAccessor;
		}

		public function getOriginAccessor ():OriginAccessor {

			return $this->originAccessor;
		}

		protected function allMethodAction (callable $action):array {

			$methodList = [];

			$concrete = $this->originAccessor->getCallDetails()->getConcrete();

			foreach ($this->safeMethods($concrete) as $methodName)

				$methodList[$methodName] = function (array $argumentList) use ($action) { // decoratorHydrator expects a closure with this signature

					return $action($methodName, $argumentList);
				};

			return $methodList;
		}

		private function safeMethods (object $instance):array {

			$methods = get_class_methods($instance);

			unset($methods[
				array_search(Container::CLASS_CONSTRUCTOR, $methods)
			]);

			return $methods;
		}
	}
?>