<?php

	namespace Tilwa\Response\Format;

	use SuperClosure\Serializer;

	class Redirect extends AbstractRenderer {

		private $destination;

		function __construct(string $handler, Closure $destination) {

			$this->destination = (new Serializer())->serialize($destination); // liquefy it so it can be cached later under previous requests

			$this->handler = $handler;
		}

		public function render() {
			
			$callable = (new Serializer)->unserialize($this->destination)->bindTo($this, $this); // so dev can have access to `rawResponse`

			$parameters = $this->container->getMethodParameters($this->destination); // autowiring in case next location will be dictated by another library

			return header('Location: '. call_user_func_array($callable, $parameters) );
		}
	}
?>