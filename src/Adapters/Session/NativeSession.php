<?php
	namespace Suphle\Adapters\Session;

	use Suphle\Contracts\IO\{Session as SessionContract, EnvAccessor};

	class NativeSession implements SessionContract {

		public function __construct (private readonly EnvAccessor $envAccessor) {

			if ($this->safeToStart()) $this->startNew();
		}

		protected function safeToStart ():bool {

			return session_status() == PHP_SESSION_NONE && !headers_sent();
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

			setcookie(
				session_name(), session_id(),

				[
					"expires" => time() + $this->envAccessor->getField("SESSION_DURATION")
				]
			);
		}
	}
?>