<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\LoginActions;

	class EmailPasswordRepo implements LoginActions {

		public function __construct (Orm $orm, RequestDetails $requestDetails, SessionStorage $continuityMethod) { // the endpoints using this probably don't care what variant of AuthStorage did the job
		}

		public function compareCredentials ():bool {}

		// session/jwt values are set, depending on auth guard
		public function successLogin () {}

		public function failedLogin () {}
	}