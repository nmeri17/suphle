<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Models;

	use Tilwa\Adapters\Orms\Eloquent\Factories\MultiEditorFactory;

	use Illuminate\Database\Eloquent\Factories\Factory;

	class ActiveEditors extends BaseModel {

		protected static function newFactory ():Factory {

			return MultiEditorFactory::new();
		}

		public function editable () {

			return $this->morphTo();
		}
	}
?>