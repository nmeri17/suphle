<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\Router as RouterConfig;

	use Suphle\Middleware\Handlers\{FinalHandlerWrapper, CsrfMiddleware};

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Bridge\Laravel\Routing\ModuleRouteMatcher;

	class Router implements RouterConfig {

		public function apiPrefix():string {

			return "api";
		}

		/**
		 * {@inheritdoc}
		*/
		public function apiStack ():array {

			return [];
		}

		/**
		 * {@inheritdoc}
		*/
		public function browserEntryRoute ():?string {

			return null;
		}

		/**
		 * {@inheritdoc}
		*/
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

		/**
		 * {@inheritdoc}
		*/
		public function externalRouters ():array {

			return [ModuleRouteMatcher::class];
		}
	}
?>