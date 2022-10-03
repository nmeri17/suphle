<?php
	namespace Suphle\Hydration\Structures;

	use Suphle\Contracts\Hydration\{InterfaceCollection, DecoratorChain};

	use Suphle\Contracts\{Presentation\HtmlParser, Queues\Adapter as QueueAdapter, Modules\ControllerModule };

	use Suphle\Contracts\Exception\{FatalShutdownAlert, AlertAdapter};

	use Suphle\Contracts\IO\{Session, MailClient, EnvAccessor, CacheManager};

	use Suphle\Contracts\Requests\{RequestValidator, StdInputReader, FileInputReader};

	use Suphle\Contracts\Database\{OrmDialect, OrmReplicator, OrmTester};

	use Suphle\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

	use Suphle\Contracts\Auth\{AuthStorage, ModuleLoginHandler, ColumnPayloadComparer, UserContract};

	use Suphle\Contracts\Config\{AuthContract, Database, DecoratorProxy, ExceptionInterceptor, Transphporm as TransphpormConfig, Laravel as LaravelConfig, Console as ConsoleContract, Flows as FlowConfig, ContainerConfig as IContainerConfig, CacheClient as CacheConfig, ComponentTemplates};

	use Suphle\Contracts\IO\Image\{ImageThumbnailClient, InferiorImageClient, ImageLocator, InferiorOperationHandler, ThumbnailOperationHandler};

	use Suphle\IO\Image\{InterfaceLoaders\ImageThumbnailLoader, SaveClients\LocalSaver};

	use Suphle\IO\Image\Operations\{DefaultInferiorHandler, DefaultThumbnailHandler};

	use Suphle\IO\{Mailing\MailClientLoader, Env\DatabaseEnvReader};

	use Suphle\IO\Cache\AdapterLoader as CacheAdapterLoader;

	use Suphle\Auth\{LoginHandlerInterfaceLoader, EmailPasswordComparer, Storage\SessionStorage};

	use Suphle\Adapters\Orms\Eloquent\{ UserEntityLoader, ModelReplicator, OrmLoader, DatabaseTester as EloquentTester, Models\User as EloquentUser};

	use Suphle\Adapters\Image\Optimizers\NativeReducerClient;

	use Suphle\Adapters\{Exception\Bugsnag, Session\NativeSession, Markups\Transphporm as TransphpormAdapter};

	use Suphle\Queues\AdapterLoader as QueueAdapterLoader;

	use Suphle\Request\{NativeInputReader, ValidatorLoader, NativeFileReader};

	use Suphle\Config\{Auth, Transphporm, Laravel, ExceptionConfig, Console as CliConsole, PDOMysqlKeys, DefaultFlowConfig, ProxyManagerConfig, DefaultCacheConfig, DefaultTemplateConfig, ContainerConfig};

	use Suphle\Modules\ControllerModuleApi;

	use Suphle\Hydration\Structures\BaseDecorators;

	use Suphle\Bridge\Laravel\InterfaceLoaders\{LaravelAppLoader, ArtisanLoader};

	use Suphle\Exception\Jobs\MailShutdownAlert;

	use Psr\Http\Client\ClientInterface as OutgoingRequest;

	use GuzzleHttp\Client as GuzzleClient;

	class BaseInterfaceCollection implements InterfaceCollection {

		private $delegateInstances = [];

		public function getLoaders():array {

			return [

				CacheManager::class => CacheAdapterLoader::class,

				ImageThumbnailClient::class => ImageThumbnailLoader::class,

				LaravelContainer::class => LaravelAppLoader::class,

				LaravelArtisan::class => ArtisanLoader::class,

				MailClient::class => MailClientLoader::class,

				ModuleLoginHandler::class => LoginHandlerInterfaceLoader::class,
				
				OrmDialect::class => OrmLoader::class,

				QueueAdapter::class => QueueAdapterLoader::class,

				RequestValidator::class => ValidatorLoader::class
			];
		}

		public function simpleBinds():array {

			return [

				AlertAdapter::class => Bugsnag::class,

				AuthStorage::class => SessionStorage::class,

				ColumnPayloadComparer::class => EmailPasswordComparer::class,

				ControllerModule::class => ControllerModuleApi::class,

				DecoratorChain::class => BaseDecorators::class,

				EnvAccessor::class => DatabaseEnvReader::class,

				FatalShutdownAlert::class => MailShutdownAlert::class,

				FileInputReader::class => NativeFileReader::class,

				HtmlParser::class => TransphpormAdapter::class,

				ImageLocator::class => LocalSaver::class,

				InferiorOperationHandler::class => DefaultInferiorHandler::class,

				InferiorImageClient::class => NativeReducerClient::class,

				OutgoingRequest::class => GuzzleClient::class,

				OrmReplicator::class => ModelReplicator::class,

				OrmTester::class => EloquentTester::class,

				Session::class => NativeSession::class,

				StdInputReader::class => NativeInputReader::class,

				ThumbnailOperationHandler::class => DefaultThumbnailHandler::class,

				UserContract::class => EloquentUser::class
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

				AuthContract::class => Auth::class,

				CacheConfig::class => DefaultCacheConfig::class,

				ComponentTemplates::class => DefaultTemplateConfig::class,

				ConsoleContract::class => CliConsole::class,

				IContainerConfig::class => ContainerConfig::class,

				Database::class => PDOMysqlKeys::class,

				DecoratorProxy::class => ProxyManagerConfig::class,

				ExceptionInterceptor::class => ExceptionConfig::class,

				FlowConfig::class => DefaultFlowConfig::class,

				LaravelConfig::class => Laravel::class,

				TransphpormConfig::class => Transphporm::class
			];
		}
	}
?>