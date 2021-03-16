<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\PermissibleService;

	class NoSqlLogic implements PermissibleService {

		public function restrictAccess() {
			# whenType self::class needsAny orm model, return null
		}
	}
?>