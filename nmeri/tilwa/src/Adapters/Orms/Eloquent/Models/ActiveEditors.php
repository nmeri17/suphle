<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Models;

	use Tilwa\Adapters\Orms\Eloquent\Factories\MultiEditorFactory;

	class ActiveEditors extends BaseModel {

		protected static function newFactory ():MultiEditorFactory {

			return MultiEditorFactory::new();
		}

		public function editable () {

			return $this->morphTo();
		}
	}
?>