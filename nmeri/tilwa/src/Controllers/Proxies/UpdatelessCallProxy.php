<?php
	namespace Tilwa\Controllers\Proxies;

	class UpdatelessCallProxy extends BaseCallProxy {

		protected function yield (string $method, array $arguments) {

			return call_user_func_array([$this->activeService, $method], $arguments);
		}
	}
?>