<?php
	namespace Suphle\Contracts\IO;

	interface EnvAccessor {

		public function getField (string $name, $defaultValue = null);

		//public function setField (string $name, $value):void;
	}
?>