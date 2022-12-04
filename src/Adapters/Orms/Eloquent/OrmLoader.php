<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\Hydration\{BaseInterfaceLoader, Container};

	use Suphle\Contracts\{ Config\AuthContract, Bridge\LaravelContainer, Database\OrmDialect, Auth\AuthStorage};

	use Illuminate\Events\Dispatcher;

	class OrmLoader extends BaseInterfaceLoader {

		public function __construct(private readonly AuthContract $authContract, private readonly AuthStorage $authStorage, private readonly LaravelContainer $laravelContainer, private readonly Container $container)
  {
  }

		public function afterBind ($initialized):void {

			$this->laravelContainer->registerConcreteBindings($this->databaseBindings($initialized)); // implicitly sets connection

			$client = $initialized->getNativeClient();

			$client->setEventDispatcher($this->laravelContainer->make(Dispatcher::class));

			$client->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

			$this->injectHydrator($initialized); // just before giving this to the observers

			$initialized->registerObservers(

				$this->authContract->getModelObservers(),

				$this->authStorage
			);
		}

		public function concreteName ():string {

			return OrmBridge::class;
		}

		protected function databaseBindings (OrmDialect $initialized):array {

			return [

				"db.connection" => $initialized->getConnection(),

				"db" => $initialized->getNativeClient()->getDatabaseManager()
			];
		}

		protected function injectHydrator (OrmDialect $initialized):void {

			$authStorage = $this->authStorage;

			$authStorage->setHydrator($initialized->getUserHydrator());

			$this->container->whenTypeAny()->needsAny([

				AuthStorage::class => $authStorage
			]);
		}
	}
?>