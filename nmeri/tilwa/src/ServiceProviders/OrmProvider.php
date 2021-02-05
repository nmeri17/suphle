<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\{ServiceProvider};

	use Tilwa\Adapters\Orms\Eloquent;

	class OrmProvider extends ServiceProvider {

		public function bindArguments() {

			return [

				"credentials" => [
					'dbname' => getenv('DB_NAME'),

				    'user' => getenv('DB_USERNAME'),

				    'password' => getenv('DB_PASS'),

				    'driver' => 'pdo_mysql',
				]
			];
		}

		public function concrete():string {

			return Eloquent::class;
		}
	}
?>