<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\Router as RouterConfig;

	use Suphle\Middleware\Handlers\{FinalHandlerWrapper, CsrfMiddleware, JsonNegotiator};

	use Suphle\Middleware\Collectors\JsonNegotiatorCollector;

	use Suphle\Auth\RequestScrutinizers\{AuthenticateMetaFunnel, AuthenticateHandler, AuthorizeMetaFunnel, PathAuthorizationScrutinizer};

	use Suphle\Adapters\Orms\Eloquent\RequestScrutinizers\{AccountVerifiedFunnel, UserIsVerified};

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

		/**
		 * {@inheritdoc}
		*/
		public function collectorHandlers ():array {

			return [

				JsonNegotiatorCollector::class => JsonNegotiator::class,
			];
		}

		/**
		 * {@inheritdoc}
		*/
		public function scrutinizerHandlers ():array {

			return [

				AccountVerifiedFunnel::class => UserIsVerified::class,

				AuthenticateMetaFunnel::class => AuthenticateHandler::class,

				AuthorizeMetaFunnel::class => PathAuthorizationScrutinizer::class
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