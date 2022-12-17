<?php
	namespace Suphle\Adapters\Session;

	use Suphle\Contracts\IO\{Session as SessionContract, EnvAccessor};

	class NativeSession implements SessionContract {

		public const FLASH_KEY = "_flash_entry";

		public function __construct (protected readonly EnvAccessor $envAccessor) {

			if ($this->safeToStart()) $this->startNew();
		}

		/**
		 * Avoid "session already started" errors
		 */
		protected function safeToStart ():bool {

			return session_status() == PHP_SESSION_NONE // sessions are enabled but none exists

			&& !headers_sent();
		}

		public function setValue (string $key, $value):void {

			$_SESSION[$key] = $value;
		}

		public function setFlashValue (string $key, $value):void {

			$existingFlash = $this->getValue(self::FLASH_KEY);

			$existingFlash[$key] = $value;

			$this->setValue(self::FLASH_KEY, $existingFlash);
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

			$this->setCookieElapse($this->envAccessor->getField("SESSION_DURATION"));

			$_SESSION[self::FLASH_KEY] = [];
		}

		protected function setCookieElapse (string $incrementBy, array $cookieOptions = []):void {

			setcookie(
				session_name(), session_id(),

				array_merge([

					"expires" => time() + intval($incrementBy)
				], $cookieOptions)
			);
		}
	}
?>