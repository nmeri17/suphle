<?php

	namespace Tilwa\Contracts;

	use Orm;

	interface Authenticator {

		function __construct(Orm $databaseAdapter, string $userModel, bool $isApiRoute);

		public function getIdentifier ():int;

		public function continueSession ():void;

		public function getUser ();

		private function setUser ($user);

		public function initializeSession (int $userId):string;
	}
?>