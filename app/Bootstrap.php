<?php

	namespace App;

	use Tilwa\App\Bootstrap as InitApp;

	use AppRoutes\MainRoutes;

	use Tilwa\Contracts\Orm;

	use Adapters\Orms\Eloquent;
	
	class Bootstrap extends InitApp {

		protected function setFileSystemPaths () {

			$slash = DIRECTORY_SEPARATOR;

			$rootPath = dirname(__DIR__, 1) . $slash; // up one folder

			$this->container += [

				'viewPath' => $rootPath . 'views'. $slash

			] + compact('rootPath', 'slash');

			return $this;
		}

		public function getAppMainRoutes ():string {

			return MainRoutes::class; // each of the classes will extend RouteCollection class, therefore `$this->prefixFor(class)`. we will call all the methods on the class externally with `get_class_methods(class_name)`
		}

		protected function provider ():array {

			return [
				Orm::class => Eloquent::class
			];
		}

		protected function bootAdapters ():self {

			$this->setOrmRequirements();

			return $this;
		}

		private function setOrmRequirements ():void {

			$this->whenType(Orm::class)->needsArguments(["credentials"])
			->giveArguments([

				"credentials" => [
					'dbname' => getenv('DB_NAME'),

				    'user' => getenv('DB_USERNAME'),

				    'password' => getenv('DB_PASS'),

				    'driver' => 'pdo_mysql',
				]
			]);
		}
	}

?>