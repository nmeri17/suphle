<?php
	namespace Tilwa\Contracts\IO;

	interface Session {
		
		public function setValue (string $key, $value):void;

		public function getValue (string $key);

		public function hasKey (string $key):bool;

		public function reset ():void;

		public function startNew ():void;
	}
?>