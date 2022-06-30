<?php
	namespace Tilwa\Hydration\Structures;

	use Tilwa\Contracts\Hydration\{InterfaceCollection, DecoratorChain};

	use Tilwa\Contracts\{Presentation\HtmlParser, Queues\Adapter as QueueAdapter, Modules\ControllerModule, Exception\FatalShutdownAlert};

	use Tilwa\Contracts\IO\{Session, MailClient, EnvAccessor};

	use Tilwa\Contracts\Requests\{RequestValidator, StdInputReader, FileInputReader};

	use Tilwa\Contracts\Database\{OrmDialect, OrmReplicator, OrmTester};

	use Tilwa\Contracts\Bridge\{LaravelContainer, LaravelArtisan};

	use Tilwa\Contracts\Auth\{AuthStorage, ModuleLoginHandler};

	use Tilwa\Contracts\Config\{AuthContract, Database, DecoratorProxy, ExceptionInterceptor, Transphporm as TransphpormConfig, Laravel as LaravelConfig, Console as ConsoleContract, Flows as FlowConfig};

	use Tilwa\Contracts\IO\Image\{ImageThumbnailClient, InferiorImageClient, ImageLocator, InferiorOperationHandler, ThumbnailOperationHandler};

	use Tilwa\IO\Image\InterfaceLoaders\ImageThumbnailLoader;

	use Tilwa\IO\Image\SaveClients\LocalSaver;

	use Tilwa\IO\Image\Operations\{DefaultInferiorHandler, DefaultThumbnailHandler};

	use Tilwa\IO\{Session\NativeSession, Mailing\MailClientLoader, Env\DatabaseEnvReader};

	use Tilwa\Queues\Adapters\Resque;

	use Tilwa\Auth\{LoginHandlerInterfaceLoader, Storage\SessionStorage};

	use Tilwa\Adapters\Orms\Eloquent\{ UserEntityLoader, ModelReplicator, OrmLoader, DatabaseTester as EloquentTester};

	use Tilwa\Adapters\Markups\Transphporm as TransphpormAdapter;

	use Tilwa\Adapters\Image\Optimizers\NativeReducerClient;

	use Tilwa\Request\{NativeInputReader, ValidatorLoader, NativeFileReader};

	use Tilwa\Config\{Auth, Transphporm, Laravel, ExceptionConfig, Console as CliConsole, PDOMysqlKeys, DefaultFlowConfig, ProxyManagerConfig};

	use Tilwa\Modules\ControllerModuleApi;

	use Tilwa\Hydration\Structures\BaseDecorators;

	use Tilwa\Bridge\Laravel\InterfaceLoaders\{LaravelAppLoader, ArtisanLoader};

	use Tilwa\Exception\Jobs\MailShutdownAlert;

	use Psr\Http\{Client\ClientInterface as OutgoingRequest, Message\RequestFactoryInterface};

	use GuzzleHttp\{Psr7\HttpFactory as GuzzleHttpFactory, Client as GuzzleClient};

	class BaseInterfaceCollection implements InterfaceCollection {

		private $delegateInstances = [];

		public function getLoaders():array {

			return [
				OrmDialect::class => OrmLoader::class,

				LaravelContainer::class => LaravelAppLoader::class,

				LaravelArtisan::class => ArtisanLoader::class,

				ModuleLoginHandler::class => LoginHandlerInterfaceLoader::class,

				ImageThumbnailClient::class => ImageThumbnailLoader::class,

				RequestValidator::class => ValidatorLoader::class,

				MailClient::class => MailClientLoader::class
			];
		}

		public function simpleBinds():array {

			return [

				AuthStorage::class => SessionStorage::class,

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

				QueueAdapter::class => Resque::class,

				RequestFactoryInterface::class => GuzzleHttpFactory::class,

				Session::class => NativeSession::class,

				StdInputReader::class => NativeInputReader::class,

				ThumbnailOperationHandler::class => DefaultThumbnailHandler::class
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

				AuthContract::class => Auth::class,

				TransphpormConfig::class => Transphporm::class,

				ExceptionInterceptor::class => ExceptionConfig::class,

				ConsoleContract::class => CliConsole::class,

				Database::class => PDOMysqlKeys::class,

				FlowConfig::class => DefaultFlowConfig::class,

				DecoratorProxy::class => ProxyManagerConfig::class
			];
		}
	}
?>