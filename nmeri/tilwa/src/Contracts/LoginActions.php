<?php

	namespace Tilwa\Contracts;

	interface LoginActions {

		public function compareCredentials ():bool;

		// session/jwt values are set, depending on auth guard
		public function successLogin ();

		public function failedLogin ();
	}