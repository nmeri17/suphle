<?php
	namespace Suphle\Adapters\Session;

	use Suphle\Contracts\IO\Session as SessionContract;

	class InMemorySession implements SessionContract {

		private $store = [];

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

		public function startNew ():void {

			$this->store = [];
		}

		public function hasOldInput (string $key):bool {

			return false; // $this->hasKey(RouteManager::PREV_RENDERER)[$key]? // we don't flash data
		}

		public function getOldInput (string $key) {

			return; // see above
		}

		public function all (string $key):array {

			return $this->store;
		}

		public function only (array $keys):array {

			return array_filter($this->store, function ($key) use ($keys) {

				return in_array($key, $keys);
			}, ARRAY_FILTER_USE_KEY);
		}
	}
?>