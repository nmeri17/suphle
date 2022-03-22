<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\{ Config\AuthContract, Bridge\LaravelContainer, Database\OrmDialect};

	use Illuminate\Events\Dispatcher;

	class OrmLoader extends BaseInterfaceLoader {

		private $authContract, $laravelContainer;

		public function __construct (AuthContract $authContract, LaravelContainer $laravelContainer) {

			$this->authContract = $authContract;

			$this->laravelContainer = $laravelContainer;
		}

		public function afterBind ($initialized):void {

			$this->laravelContainer->injectBindings($this->databaseBindings($initialized)); // implicitly sets connection

			$client = $initialized->getNativeClient();

			$client->setEventDispatcher($this->laravelContainer->make(Dispatcher::class));

			$client->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

			$initialized->registerObservers($this->authContract->getModelObservers());
		}

		public function concrete ():string {

			return OrmBridge::class;
		}

		protected function databaseBindings ($initialized):array {

			return [

				"db.connection" => $initialized->getConnection(),

				"db" => $initialized->getNativeClient()
			];
		}
	}
?>