<?php
	namespace Tilwa\Tests\Mocks\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\{Models\BaseModel, Condiments\EditIntegrity};

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Tests\Models\Eloquent\Factories\MultiEditProductFactory;

	class MultiEditProduct extends BaseModel implements IntegrityModel {

		use EditIntegrity;

		protected static function newFactory ():Factory {

			return MultiEditProductFactory::new();
		}
	}
?>