<?php
	namespace Tilwa\Tests\Models\Eloquent;

	use Tilwa\Adapters\Orms\Eloquent\{Models\BaseModel, Condiments\EditIntegrity};

	class MultiEditProduct extends BaseModel {

		use EditIntegrity;

		protected static function newFactory ():Factory {

			return MultiEditorFactory::new(); // tbd
		}
	}
?>