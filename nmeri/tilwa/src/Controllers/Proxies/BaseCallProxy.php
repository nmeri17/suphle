<?php
	namespace Tilwa\Controllers\Proxies;

	abstract class BaseCallProxy {

		protected $activeService;

		abstract public function artificial__call (string $method, array $arguments);

		protected function yield (string $method, array $arguments) {

			return call_user_func_array([$this->activeService, $method], $arguments);
		}

		public function setConcrete ($instance):void {

			$this->activeService = $instance;
		}
	}
?>