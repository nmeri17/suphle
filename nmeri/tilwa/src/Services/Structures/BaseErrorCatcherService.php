<?php
	namespace Tilwa\Services\Structures;

	trait BaseErrorCatcherService {

		protected $erroneousMethod;

		public function rethrowAs ():array {

			return [];
		}

		public function failureState (string $method) {

			//
		}

		public function lastErrorMethod ():?string {

			return $this->erroneousMethod;
		}

		public function didHaveErrors (string $method):void {

			$this->erroneousMethod = $method;
		}

		public function matchesErrorMethod (string $method):bool {

			return $method == $this->erroneousMethod;
		}
	}
?>