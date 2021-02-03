<?php

	namespace Tilwa\Modules\Auth\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Modules\Auth\Controllers\HandleResets;

	use Tilwa\Http\Response\Format\{Markup, Redirect};

	class PasswordResets extends RouteCollection {

		public function _handlingClass ():string {

			return HandleResets::class;
		}
		
		public function SHOW() {
			
			return $this->_get(new Markup("showReset", "password/show-reset-form"));
		}

		public function SUBMIT__MAILh() {
			
			return $this->_post(new Redirect("sendConfirmMail", "resets/mail-success"));
		}
		
		public function MAIL__SUCCESSh() {
			
			return $this->_get(new Markup("showResetSuccess", "password/mail-success"));
		}

		public function CONFIRM__RESETh() {
			
			return $this->_get(new Markup("confirmReset", "password/confirm-reset"));
		}

		public function NEW__PASSWORDh() {
			
			return $this->_post(new Redirect("updatePassword", "/resets/password-updated"));
		}
		
		public function PASSWORD__UPDATEDh() {
			
			return $this->_get(new Markup("updateSuccess", "password/update-success"));
		}

		public function _passover():bool {
			
			return !$this->allow->isAuth();
		}
	}
?>