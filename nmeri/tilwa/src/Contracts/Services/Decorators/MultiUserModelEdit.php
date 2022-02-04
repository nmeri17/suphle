<?php
	namespace Tilwa\Contracts\Services\Decorators;

	use Tilwa\Contracts\Services\Models\IntegrityModel;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	/**
	 * Used to preserve integrity and avoid collisions by preventing overwrite between two users updating one resource at different times between when edit page is opened, and when form is submitted
	*/
	interface MultiUserModelEdit extends ServiceErrorCatcher {

		/**
		 * We expect this method to be idempotent i.e. yield same resource, be it called during get or post
		*/
		public function getResource ():IntegrityModel;

		public function setLastIntegrity (int $integrity):void;

		public function getLastIntegrity ():int;

		/**
		 * @throws EditIntegrityException
		 * 
		 * @return mixed. Can be anything caller can work with
		*/
		public function updateResource ();
	}
?>