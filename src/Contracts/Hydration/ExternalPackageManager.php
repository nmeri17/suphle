<?php
	namespace Suphle\Contracts\Hydration;

	interface ExternalPackageManager {

		public function canProvide (string $fullName):bool;

		/**
		 * @return Object, wrapped proxy of underlying service being provided
		*/
		public function manageService (string $fullName);
	}
?>