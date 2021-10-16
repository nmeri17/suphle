<?php
	namespace Tilwa\Testing;

	use Tilwa\Contracts\Auth\User;

	trait SecureUserTest {

		protected function actingAs(User $user):void {

			// updates the authStorage->getUser
		}

		protected function assertAuthenticatedAs(User $user):void {

			//
		}

		protected function assertGuest ():void {

			//
		}
	}
?>