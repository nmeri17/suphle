<?php
	namespace Tilwa\Middleware;

	use Tilwa\Contracts\{Routing\Middleware, Services\Decorators\SelectiveDependencies, Auth\User};

	class BaseMiddleware implements Middleware, SelectiveDependencies {

		public function getPermitted ():array {

			return [];
		}

		public function getRejected ():array {

			return [User::class];
		}
	}
?>