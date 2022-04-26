<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\{ Config\AuthContract, Bridge\LaravelContainer, Database\OrmDialect, Auth\AuthStorage};

	use Illuminate\Events\Dispatcher;

	class OrmLoader extends BaseInterfaceLoader {

		private $authContract, $authStorage, $laravelContainer;

		public function __construct (AuthContract $authContract, AuthStorage $authStorage, LaravelContainer $laravelContainer) {

			$this->authContract = $authContract;

			$this->authStorage = $authStorage;

			$this->laravelContainer = $laravelContainer;
		}

		public function afterBind ($initialized):void {

			$this->laravelContainer->registerConcreteBindings($this->databaseBindings($initialized)); // implicitly sets connection

			$client = $initialized->getNativeClient();

			$client->setEventDispatcher($this->laravelContainer->make(Dispatcher::class));

			$client->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

			$initialized->registerObservers(

				$this->authContract->getModelObservers(),

				$this->authStorage
			);
		}

		public function concrete ():string {

			return OrmBridge::class;
		}

		protected function databaseBindings ($initialized):array {

			return [

				"db.connection" => $initialized->getConnection(),

				"db" => $initialized->getNativeClient()->getDatabaseManager()
			];
		}
	}
?>