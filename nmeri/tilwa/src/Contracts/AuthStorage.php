<?php

	namespace Tilwa\Contracts;

	interface AuthStorage {

		private function logout ():void;

		public function loginAs ();

		// used to determine auth status during a request
		public function getIdentifier ():string;

		public function startSession (string $userId):string;

		public function resumeSession ():void;

		public function getUser ();
	}
?>