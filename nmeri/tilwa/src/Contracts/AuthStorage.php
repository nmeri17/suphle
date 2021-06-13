<?php

	namespace Tilwa\Contracts;

	use Orm;

	interface AuthStorage {

		/*public function getUser ();

		private function setUser ($user);

		public function hydrateUser ():void;*/

		public function getIdentifier ():string;

		public function setIdentifier (string $identifier):void;

		public function claimRoutes (array $paths):self;
	}
?>