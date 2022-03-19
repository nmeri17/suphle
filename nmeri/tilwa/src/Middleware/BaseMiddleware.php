<?php
	namespace Tilwa\Middleware;

	use Tilwa\Contracts\{Routing\Middleware, Services\Decorators\SelectiveDependencies, Auth\UserContract};

	use Tilwa\Request\RequestDetails;

	abstract class BaseMiddleware implements Middleware, SelectiveDependencies {

		protected $requestDetails;

		public function __construct (RequestDetails $requestDetails) {

			$this->requestDetails = $requestDetails;
		}

		public function getPermitted ():array {

			return [];
		}

		public function getRejected ():array {

			return [UserContract::class];
		}
	}
?>