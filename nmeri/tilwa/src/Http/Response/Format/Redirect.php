<?php

	namespace Tilwa\Http\Response\Format;

	use SuperClosure\Serializer;

	class Redirect extends AbstractRenderer {

		private $destination;

		function __construct(string $handler, Closure $destination, array $middleware) {
			
			$this->middleware = $middleware;

			$this->destination = (new Serializer())->serialize($destination); // liquefy it so it can be cached later under previous requests

			$this->handler = $handler;
		}

		public function render() {
			
			$callable = (new Serializer)->unserialize($this->destination)->bindTo($this, $this);

			return header('Location: '. $callable($this->rawResponse));
		}
	}
?>