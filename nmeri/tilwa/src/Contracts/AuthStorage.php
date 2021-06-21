<?php

	namespace Tilwa\Contracts;

	interface AuthStorage {

		private function logout ():void;

		public function loginAs ();

		public function getId ():string;

		public function startSession (string $userId):string;

		public function resumeSession ():void;

		public function getUser ();
	}
?>