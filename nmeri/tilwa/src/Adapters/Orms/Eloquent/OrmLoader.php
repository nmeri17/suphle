<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Contracts\Config\{ Auth as AuthConfig, Bridge\LaravelContainer};

	use Illuminate\Events\Dispatcher;

	class OrmLoader extends BaseInterfaceLoader {

		private $authConfig, $laravelContainer;

		public function __construct (AuthConfig $authConfig, LaravelContainer $laravelContainer) {

			$this->authConfig = $authConfig;

			$this->laravelContainer = $laravelContainer;
		}

		public function afterBind ($initialized):void {

			$connection = $initialized->getConnection();

			$this->laravelContainer->bind("db", function () use ($connection) { // bind this for any laravel class e.g. Schema facade that relies on its value

				return $connection;
			});

			$connection->setEventDispatcher(new Dispatcher($this->laravelContainer));

			$connection->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

			$initialized->registerObservers($this->authConfig->getModelObservers());
		}

		public function concrete ():string {

			return OrmBridge::class;
		}
	}
?>