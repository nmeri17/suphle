<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\{ Config\Auth as AuthConfig, Bridge\LaravelContainer, Database\OrmDialect};

	use Illuminate\Events\Dispatcher;

	class OrmLoader extends BaseInterfaceLoader {

		private $authConfig, $laravelContainer;

		public function __construct (AuthConfig $authConfig, LaravelContainer $laravelContainer) {

			$this->authConfig = $authConfig;

			$this->laravelContainer = $laravelContainer;
		}

		public function afterBind ($initialized):void {

			$client = $initialized->getNativeClient();

			$this->laravelContainer->injectBindings($this->databaseBindings());

			$client->setEventDispatcher($this->laravelContainer->make(Dispatcher::class));

			$client->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

			$initialized->registerObservers($this->authConfig->getModelObservers());
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