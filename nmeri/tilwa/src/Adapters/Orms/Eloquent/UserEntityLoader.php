<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\{BaseInterfaceLoader, Container};

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	class UserEntityLoader extends BaseInterfaceLoader {

		private $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function bindArguments():array {

			$this->container->getClass(OrmDialect::class); // we just want to ensure user model isn't obtained without consulting ormDialect

			// using this method instead of the constructor since ormBridge equally references authStorage, which would put this in a recursive loop
			return [];
		}

		public function concrete():string {

			return EloquentUser::class;
		}
	}
?>