<?php
	namespace Suphle\Contracts\IO;

	interface EnvAccessor {

		public function getField (string $name);

		public function setField (string $name, $value):void;
	}
?>