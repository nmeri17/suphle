<?php
	namespace Suphle\Contracts\Config;

	interface Flows extends ConfigMarker {

		/**
		 * Types returned from here should be mapped to methods that enable us know type of the underlying objects in a given collection e.g. Cars, Jobs. This can then enable us tag all objects matching this tag in the cache or watch for updates
		*/
		public function contentTypeIdentifier ():array;

		/**
		 * When unnecessary, prevent the overhead of hydrating this for each request
		*/
		public function isEnabled ():bool;
	}
?>