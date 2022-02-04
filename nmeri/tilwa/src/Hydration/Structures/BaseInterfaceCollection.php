<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\{InterfaceCollection, DecoratorChain};

	use Tilwa\Contracts\{HtmlParser, Database\OrmDialect, Requests\RequestValidator, Queues\Adapter as QueueAdapter, Bridge\LaravelContainer, Modules\ControllerModule};

	use Tilwa\Contracts\Auth\{AuthStorage, User as UserEntity, UserHydrator as IUserHydrator};

	use Tilwa\Contracts\Config\{Auth as AuthConfig, Transphporm as TransphpormConfig, Laravel as LaravelConfig, ExceptionInterceptor};

	use Tilwa\InterfaceLoader\{OrmLoader, LaravelAppLoader};

	use Tilwa\Queues\Adapters\Resque;

	use Tilwa\Auth\Storage\SessionStorage;

	use Tilwa\Auth\Models\Eloquent\{UserHydrator as EloquentUserHydrator, User as EloquentUser};

	use Tilwa\Adapters\Markups\Transphporm as TransphpormAdapter;

	use Tilwa\Request\Validators\RakitValidator;

	use Tilwa\Config\{Auth, Transphporm, Laravel, ExceptionConfig};

	use Tilwa\Modules\ControllerModuleApi;

	use Tilwa\Hydration\Structures\BaseDecorators;

	use Psr\Http\{Client\ClientInterface, Message\RequestFactoryInterface };

	use GuzzleHttp\{Psr7\HttpFactory as GuzzleHttpFactory, Client as GuzzleClient};

	class BaseInterfaceCollection implements InterfaceCollection {

		private $delegateInstances = [];

		public function getLoaders():array {

			return [
				OrmDialect::class => OrmLoader::class,

				LaravelContainer::class => LaravelAppLoader::class,
			];
		}

		public function simpleBinds():array {

			return [

				HtmlParser::class => TransphpormAdapter::class,

				UserEntity::class => EloquentUser::class,

				AuthStorage::class => SessionStorage::class,

				RequestValidator::class => RakitValidator::class,

				QueueAdapter::class => Resque::class,

				ControllerModule::class => ControllerModuleApi::class,

				DecoratorChain::class => BaseDecorators::class,

				RequestFactoryInterface::class => GuzzleHttpFactory::class,

				ClientInterface::class => GuzzleClient::class
			];
		}

		/**
		 * @param {interfaces} Assoc array of interfaces and their concretes
		*/
		public function delegateHydrants (array $interfaces):void {

			$this->delegateInstances = $interfaces;
		}

		public function getDelegatedInstances ():array {

			return $this->delegateInstances;
		}

		public function getConfigs ():array {
			
			return [

				LaravelConfig::class => Laravel::class,

				AuthConfig::class => Auth::class,

				TransphpormConfig::class => Transphporm::class,

				IUserHydrator::class => EloquentUserHydrator::class,

				ExceptionInterceptor::class => ExceptionConfig::class
			];
		}
	}
?>