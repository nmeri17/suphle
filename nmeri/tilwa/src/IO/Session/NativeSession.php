<?php
	namespace Tilwa\IO\Session;

	use Tilwa\Contracts\IO\Session as SessionContract;

	class NativeSession implements SessionContract {

		function __construct() {

			if ($this->noActiveSession()) $this->startNew();
		}

		protected function noActiveSession ():bool {

			return session_status() == PHP_SESSION_NONE /*&& !headers_sent()*/;
		}

		public function setValue (string $key, $value):void {

			$_SESSION[$key] = $value;
		}

		public function getValue (string $key) {

			return $_SESSION[$key];
		}

		public function hasKey (string $key):bool {

			return array_key_exists($key, $_SESSION);
		}

		public function reset ():void {

			$_SESSION = [];

			session_destroy();
		}

		public function startNew ():void {

			session_start();
		}
	}
?>