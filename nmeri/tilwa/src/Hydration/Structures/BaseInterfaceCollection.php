<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\{InterfaceCollection, DecoratorChain};

	use Tilwa\Contracts\{HtmlParser, Requests\RequestValidator, Queues\Adapter as QueueAdapter, Modules\ControllerModule};

	use Tilwa\Contracts\Database\{OrmDialect, OrmReplicator};

	use Tilwa\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

	use Tilwa\Contracts\Auth\{AuthStorage, UserContract, UserHydrator as IUserHydrator, ModuleLoginHandler};

	use Tilwa\Contracts\Config\{Auth as AuthConfig, Transphporm as TransphpormConfig, Laravel as LaravelConfig, ExceptionInterceptor, Console as ConsoleContract};

	use Tilwa\Contracts\IO\{ImageThumbnailContract, InferiorImageContract, ImageLocator};

	use Tilwa\IO\Image\InterfaceLoaders\{InferiorImageLoader, ImageThumbnailLoader};

	use Tilwa\IO\Image\SaveClients\LocalSaver;

	use Tilwa\Queues\Adapters\Resque;

	use Tilwa\Auth\{LoginHandlerInterfaceLoader, Storage\SessionStorage};

	use Tilwa\Adapters\Orms\Eloquent\{UserHydrator as EloquentUserHydrator, User as EloquentUser, ModelReplicator, OrmLoader};

	use Tilwa\Adapters\Markups\Transphporm as TransphpormAdapter;

	use Tilwa\Request\Validators\RakitValidator;

	use Tilwa\Config\{Auth, Transphporm, Laravel, ExceptionConfig, Console as CliConsole};

	use Tilwa\Modules\ControllerModuleApi;

	use Tilwa\Hydration\Structures\BaseDecorators;

	use Tilwa\Bridge\Laravel\InterfaceLoaders\{LaravelAppLoader, ArtisanLoader};

	use Psr\Http\Client\ClientInterface;

	use Psr\Http\Message\{ServerRequestFactoryInterface, RequestFactoryInterface};

	use GuzzleHttp\{Psr7\HttpFactory as GuzzleHttpFactory, Client as GuzzleClient};

	use Laminas\Diactoros\ServerRequestFactory;

	class BaseInterfaceCollection implements InterfaceCollection {

		private $delegateInstances = [];

		public function getLoaders():array {

			return [
				OrmDialect::class => OrmLoader::class,

				LaravelContainer::class => LaravelAppLoader::class,

				LaravelArtisan::class => ArtisanLoader::class,

				ModuleLoginHandler::class => LoginHandlerInterfaceLoader::class,

				InferiorImageContract::class => InferiorImageLoader::class,

				ImageThumbnailContract::class => ImageThumbnailLoader::class
			];
		}

		public function simpleBinds():array {

			return [

				HtmlParser::class => TransphpormAdapter::class,

				UserContract::class => EloquentUser::class,

				AuthStorage::class => SessionStorage::class,

				RequestValidator::class => RakitValidator::class,

				QueueAdapter::class => Resque::class,

				ControllerModule::class => ControllerModuleApi::class,

				DecoratorChain::class => BaseDecorators::class,

				RequestFactoryInterface::class => GuzzleHttpFactory::class,

				ServerRequestFactoryInterface::class => ServerRequestFactory::class,

				ClientInterface::class => GuzzleClient::class,

				OrmReplicator::class => ModelReplicator::class,

				ImageLocator::class => LocalSaver::class
			];
		}

		/**
		 * Interfaces given here are telling the hydrator that they have another way of materializing (aside from simply hydrating concrete like for everyone else)
		 * 
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

				ExceptionInterceptor::class => ExceptionConfig::class,

				ConsoleContract::class => CliConsole::class
			];
		}
	}
?>