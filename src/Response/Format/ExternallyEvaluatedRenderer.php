<?php
	namespace Suphle\Response\Format;

	/**
	 * Expects response to have been processed to desired format by the consumer/content producer
	*/
	class ExternallyEvaluatedRenderer extends GenericRenderer {

		public function render ():string {

			return $this->rawResponse;
		}
	}
?>