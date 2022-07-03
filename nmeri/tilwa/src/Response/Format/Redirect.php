<?php
	namespace Tilwa\Response\Format;

	use Tilwa\Hydration\Container;

	use Opis\Closure\{SerializableClosure, serialize, unserialize};

	class Redirect extends GenericRenderer {

		private $destination;

		protected $container;

		/**
		 * @param Since PDO instances can't be serialized, when using this renderer with PDO in scope, wrap this parameter in a curried/doubly wrapped function
		 
		 Arguments for the eventual function is autowired and the return value is used as new request location

		 Function is bound to this object instance
		*/
		public function __construct(string $handler, callable $destination) {

			$wrapper = new SerializableClosure($destination);

			$this->destination = serialize($wrapper); // liquefy it so it can be cached later under previous requests

			$this->handler = $handler;

			$this->statusCode = 302;
		}

		public function render ():string {
			
			$deserialized = unserialize($this->destination)->getClosure();

			$url = $this->invokeDestination($deserialized);

			$isCurried = is_callable($url);

			if ($isCurried) $url = $this->invokeDestination($url);

			return $this->headers["Location"] = $url;
		}

		private function invokeDestination (callable $outerFunction):string {

			$parameters = $this->container->getMethodParameters($outerFunction); // autowiring in case next location will be dictated by another library

			$bound = $outerFunction->bindTo($this, $this /*access protected properties*/); // so dev can have access to `rawResponse`

			return call_user_func_array($bound, $parameters);
		}

		public function dependencyMethods ():array {

			return array_merge(parent::dependencyMethods(), [

				"setContainer"
			]);
		}

		public function setContainer (Container $container):void {

			$this->container = $container;
		}
	}
?>