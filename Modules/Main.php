<?php

	namespace Modules;

	use Tilwa\App\Bootstrap;

	use AppRoutes\MainRoutes;

	use Tilwa\Contracts\Orm;

	use Adapters\Orms\Eloquent;

	use Tilwa\Http\Response\Templating\TemplateEngine;
	
	class Main extends Bootstrap {

		protected function setFileSystemPaths () {

			$slash = DIRECTORY_SEPARATOR;

			$rootPath = dirname(__DIR__, 1) . $slash; // up one folder

			$this->container += [

				'viewPath' => $rootPath . 'views'. $slash

			] + compact('rootPath', 'slash');

			return $this;
		}

		public function browserMainRoute ():string {

			return MainRoutes::class;
		}

		public function apiStack ():array {

			return [
				"v1" => $this->getClass(RouteManager::class) // we wanna call api mirror here
			];
		}

		protected function provider ():array {

			return [
				Orm::class => Eloquent::class,

				HtmlParser::class => TemplateEngine::class
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