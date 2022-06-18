<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	use Tilwa\Middleware\Handlers\{FinalHandlerWrapper, CsrfMiddleware};

	use Tilwa\Auth\Storage\TokenStorage;

	abstract class Router implements RouterConfig {

		public function apiPrefix():string {

			return "api";
		}

		/**
		 * {@inheritdoc}
		*/
		public function apiStack ():array {

			return [];
		}

		abstract public function browserEntryRoute ():string;

		// list in ascending order of execution
		public function defaultMiddleware():array {

			return [
				CsrfMiddleware::class,
				
				FinalHandlerWrapper::class
			];
		}

		public function mirrorsCollections ():bool {

			return false;
		}

		public function mirrorAuthenticator ():string {

			return TokenStorage::class;
		}
	}
?>