<?php
	namespace Suphle\Response\Format;

	use Suphle\Request\PayloadStorage;

	class Json extends GenericRenderer {

		protected bool $shouldDeferValidationFailure = false;

		public function __construct(string $handler) {

			$this->handler = $handler;

			$this->setHeaders(200, [

				PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
			]);
		}

		public function render():string {

			return $this->renderJson();
		}

		/**
		 * {@inheritdoc}
		 */
		public function deferValidationContent ():bool {

			return false;
		}
	}
?>