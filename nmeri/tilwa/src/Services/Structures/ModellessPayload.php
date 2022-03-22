<?php
	namespace Tilwa\Services\Structures;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Services\InterceptsExternalPayload;

	/**
	 * Aside handling requests that don't map to models/entities, this is useful for things like callback endpoints where a user is waiting for feedback on our end, but obviously not on the automated, calling service's end. In such cases, mere validation errors won't cut it. We need to respond to the waiting services with something to complete user flow
	*/
	abstract class ModellessPayload extends InterceptsExternalPayload {

		protected $payloadStorage;

		public function __construct (PayloadStorage $payloadStorage) {

			$this->payloadStorage = $payloadStorage;
		}
	}
?>