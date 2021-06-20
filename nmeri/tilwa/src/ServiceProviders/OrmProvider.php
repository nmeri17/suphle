<?php

	namespace Tilwa\ServiceProviders;

	use Tilwa\App\ServiceProvider;

	use Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Config\{Orm as OrmConfig, Auth as AuthConfig};

	class OrmProvider extends ServiceProvider {

		private $authConfig;

		public function __construct (AuthConfig $authConfig) {

			$this->authConfig = $authConfig;
		}

		public function afterBind($initialized):void {

			$initialized->registerObservers($this->authConfig->getModelObservers());
		}

		public function concrete():string {

			return Eloquent::class;
		}
	}
?>