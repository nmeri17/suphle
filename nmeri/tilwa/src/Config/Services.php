<?php

	namespace Tilwa\Config;

	use Tilwa\Contracts\{HtmlParser, Database\Orm, Requests\RequestValidator, Queues\Adapter as QueueAdapter, Config\Services as ServicesContract};

	use Tilwa\Contracts\Auth\{AuthStorage, User};

	use Tilwa\InterfaceLoader\{OrmLoader, LaravelAppLoader};

	use Tilwa\Queues\Adapters\Resque;

	use Tilwa\Auth\{Storage\SessionStorage, Models\Eloquent\User as EloquentUser};

	use Tilwa\Adapters\Markups\Transphporm;

	use Tilwa\Request\Validators\RakitValidator;

	class Services implements ServicesContract {

		public function lifecycle():bool {

			return false; // test probably wants this on
		}

		public function getLoaders():array {

			return [
				Orm::class => OrmLoader::class,

				HtmlParser::class => Transphporm::class,

				AuthStorage::class => SessionStorage::class,

				RequestValidator::class => RakitValidator::class,

				QueueAdapter::class => Resque::class,

				LaravelApp::class => LaravelAppLoader::class,

				User::class => EloquentUser::class
			];
		}

		public function usesLaravelPackages ():bool {

			return true;
		}
	}
?>