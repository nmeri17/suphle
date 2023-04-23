<?php
	namespace Suphle\Adapters\Session;

	use Suphle\Contracts\IO\Session as SessionContract;

	class InMemorySession implements SessionContract {

		public const FLASH_KEY = "_flash_entry";

		protected array $store = [];

		public function setValue (string $key, $value):void {

			$this->store[$key] = $value;
		}

		public function getValue (string $key) {

			return $this->hasKey($key)? $this->store[$key]: null;
		}

		public function hasKey (string $key):bool {

			return array_key_exists($key, $this->store);
		}

		// to make it compatible with TestResponseBridge where we intend to use it
		public function set (string $key, $value):void {

			$this->setValue($key, $value);
		}

		public function get (string $key) {

			return $this->getValue($key);
		}

		public function has (string $key):bool {

			return $this->hasKey($key);
		}

		public function reset ():void {

			$this->store = [];
		}

		public function prolongSession (array $cookieOptions = []):void {

			//
		}

		public function resetOldInput ():void {

			$this->store[self::FLASH_KEY] = [];
		}

		public function setFlashValue (string $key, $value):void {

			$existingFlash = $this->getValue(self::FLASH_KEY);

			$existingFlash[$key] = $value;

			$this->setValue(self::FLASH_KEY, $existingFlash);
		}

		public function hasOldInput (string $key):bool {

			return array_key_exists($key, $this->getValue(self::FLASH_KEY));
		}

		public function getOldInput (string $key) {

			return $this->getValue(self::FLASH_KEY)[$key];
		}

		public function all ():array {

			return $this->allSessionEntries();
		}

		public function allSessionEntries ():array {

			return $this->store;
		}

		public function only (array $keys):array {

			return array_filter($this->store, fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
		}
	}
?>