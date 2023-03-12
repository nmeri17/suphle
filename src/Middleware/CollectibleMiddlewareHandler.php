<?php
	namespace Suphle\Middleware;

	use Suphle\Contracts\Routing\Middleware;

	abstract class CollectibleMiddlewareHandler implements Middleware {

		protected array $collectors = [];

		public function addCollector (MiddlewareCollector $collector):void {

			$this->collectors[] = $collector;
		}
	}
?>