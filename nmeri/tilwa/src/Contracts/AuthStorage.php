<?php

	namespace Tilwa\Contracts;

	interface AuthStorage {

		public function logout ():void;

		public function loginAs (string $value);

		public function getId ():string;

		public function startSession (string $userId):string;

		public function resumeSession ():void;

		public function getUser ();
	}
?>