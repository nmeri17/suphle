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

			$route = $this->_get(new Markup("showRegister", "auth/register-form"));

			$flow = $route->getFlow(); // pulls anticipate(linksTo)/optimistic fetch(reloadsWith) depending on http request method

			$previous = $flow->lastResponseNodes();

			$flow->linksTo("/submit-register", [
				
				"nodeA" => $previous->get("nodeC") // just sets it as the active i.e. doesn't actually do any getting
				->includesPagination("path.to.next_url") // find the controller servicing this path

				->isStatic("300") // means this won't be cleared on hit and won't be loaded by subsequent flows matching the same builder
			])
			->linksTo("/categories/*", [

				"data" => $previous->collectionNode("nodeD") // assumes we're coming from the category page

				->eachAttribute("key") // works like `reduce`

				->pipeTo(\Service\Name::class, "method"), // will create multiple versions of [data] for this route alone. when a request where the wildcard matches [key], the matching data will be plugged here
				// so we need a `resolvePlaceholder` and `interactsWithPlaceholders` method on the flow object
				// should recursively load [key] but only match request to the last one?

				"nodeE" => $flow->dateOnHit("time_format") // evaluated at runtime. so we need a hasDateOnHit
			])
			->linksTo("/store/*", [

				"data" => $previous->collectionNode("nodeB")

				->eachAttribute("key")

				->oneOf(\Service\Name::class, "method", "key instead of id") // same as [pipeTo], but is sent in bulk to the service rather than one after the other. service is expected to do a `whereIn`
				// during fetch, we pull just those matching [key]
			])
			->linksTo("/orders/sort/*/*", [

				"data" => $flow->fromService(\Service\Orders::class, "method", $previous->get("store.id")) // for unknowns not part of previous response. also works on [collectionNode]

				->eachAttribute("key")

				->inRange(\Service\Name::class, "method", "column") // or [dateRange]
			]);
			
			return $route;
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