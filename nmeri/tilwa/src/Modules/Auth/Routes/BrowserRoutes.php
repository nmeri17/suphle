<?php

	namespace Tilwa\Modules\Auth\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Modules\Auth\Controllers\HandleAuth;

	use Tilwa\Http\Response\Format\{Markup, Redirect};

	class BrowserRoutes extends RouteCollection {
		
		public function _prefixCurrent() {
			
			return "auth";
		}

		public function _handlingClass ():string {

			return HandleAuth::class;
		}
		
		public function SHOW__LOGINh() {
			
			return $this->_get(new Markup("showLogin", "auth/login-form"));
		}

		public function SUBMIT__LOGINh() {
			
			return $this->_post(new Redirect("handleLogin", "/"));
		}

		public function SHOW__REGISTERh() {

			$renderer = new Markup("showRegister", "auth/register-form");

			$flow = new ControllerFlows;

			$flow->linksTo("/submit-register", [
				
				"nodeA" => $flow->previousResponse()

				->getNode("C")

				->includesPagination("path.to.next_url")

				->surviveFor("300")
			])
			->linksTo("/categories/*", [

				"data" => $flow->previousResponse()->collectionNode("nodeD") // assumes we're coming from the category page

				->eachAttribute("key")

				->pipeTo(\Service\Name::class, "method"), 
				// so we need a `resolvePlaceholder` and `interactsWithPlaceholders` method on the flow object
			])
			->linksTo("/store/*", [

				"data" => $flow->previousResponse()->collectionNode("nodeB")

				->eachAttribute("key")

				->oneOf(\Service\Name::class, "method", "key instead of id")
			])
			->linksTo("/orders/sort/*/*", [

				"data" => $flow->fromService(\Service\Orders::class, "method", $flow->previousResponse()->getNode("store.id"))

				->eachAttribute("key")

				->inRange(\Service\Name::class, "method")
			]);

			return $this->_get($renderer->setFlow($flow));
		}
		
		public function SUBMIT__REGISTERh() {
			
			return $this->_post(new Redirect("submitRegister", "auth/check-verify-mail"));
		}
		
		public function CHECK__VERIFY__MAILh() {
			
			return $this->_get(new Markup("confirmReset", "password/confirm-reset"));
		}
		
		public function VERIFY__EMAILh() {
			
			return $this->_get(new Redirect("verifyEmail", "auth/register-complete"));
		}
		
		public function REGISTER__COMPLETEh() {
			
			return $this->_get(new Markup("registrationComplete", "auth/register-complete"));
		}
		
		public function resets() {
			
			return $this->_prefixFor(PasswordResets::class);
		}
		
		public function _passover():bool {
			
			return !$this->allow->isAuth();
		}
	}
?>