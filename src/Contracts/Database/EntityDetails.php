<?php
	namespace Suphle\Contracts\Database;

	interface EntityDetails {

		public function normalizeIdentifier (object $model, string $prefix = ""):string;
	}
?>