<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Services as ServicesContract;

	use Tilwa\Contracts\{Orm, HtmlParser, Authenticator, RequestValidator, QueueManager};

	use Tilwa\ServiceProviders\{OrmProvider, AuthenticatorProvider, HtmlTemplateProvider, RequestValidatorProvider, QueueProvider, LaravelAppProvider};

	class Services implements ServicesContract {

		public function lifecycle():bool {

			return false; // test probably wants this on
		}

		public function getProviders():array {

			return [
				Orm::class => OrmProvider::class,

				HtmlParser::class => HtmlTemplateProvider::class,

				Authenticator::class => AuthenticatorProvider::class,

				RequestValidator::class => RequestValidatorProvider::class,

				QueueManager::class => QueueProvider::class,

				LaravelApp::class => LaravelAppProvider::class
			];
		}
	}
?>