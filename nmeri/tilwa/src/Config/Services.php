<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Services as ServicesContract;

	use Tilwa\Contracts\{Database\Orm, HtmlParser, Requests\RequestValidator, QueueManager};

	use Tilwa\Contracts\Auth\{AuthStorage, User};

	use Tilwa\InterfaceLoader\{OrmLoader, AuthStorageLoader, HtmlTemplateLoader, RequestValidatorLoader, QueueLoader, LaravelAppLoader, UserEntityLoader};

	class Services implements ServicesContract {

		public function lifecycle():bool {

			return false; // test probably wants this on
		}

		public function getLoaders():array {

			return [
				Orm::class => OrmLoader::class,

				HtmlParser::class => HtmlTemplateLoader::class,

				AuthStorage::class => AuthStorageLoader::class,

				RequestValidator::class => RequestValidatorLoader::class,

				QueueManager::class => QueueLoader::class,

				LaravelApp::class => LaravelAppLoader::class,

				User::class => UserEntityLoader::class
			];
		}

		public function usesLaravelPackages ():bool {

			return true;
		}
	}
?>