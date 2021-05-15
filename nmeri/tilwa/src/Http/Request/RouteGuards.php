<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\Authenticator;

	class RouteGuards {

		private $authenticator;
		
		// pull any needed services here. the individual methods themselves won't be wired in
		function __construct(Authenticator $authenticator) {
			
			$this->authenticator = $authenticator;
		}

		// this guy should be on his own so he can get custom response header and renderer
		public function isAuth():bool {
			
			return !is_null($this->authenticator->getUser());
		}
	}
?>