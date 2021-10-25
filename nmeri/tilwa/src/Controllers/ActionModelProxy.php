<?php

	namespace Tilwa\Controllers;

	use Tilwa\Errors\IllegalCaller;

	class ActionModelProxy {

		private $builderWrapper;

		public function __construct(ControllerModel $builderWrapper) {

			$this->builderWrapper = $builderWrapper;
		}

		public function __call(string $method, $arguments) {

			$caller = end(debug_backtrace(2, 2))["class"]; // [__call, ourGuy]

			if ($caller instanceof Executable)

				throw new IllegalCaller(get_class($caller));
				
			return call_user_func_array([$this->builderWrapper->getBuilder(), $method], $arguments);
		}
	}
?>