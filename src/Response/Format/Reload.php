<?php
	namespace Suphle\Response\Format;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Request\PayloadStorage;

	use Suphle\Response\PreviousResponse;

	#[VariableDependencies([ "setPreviousResponse" ])]
	class Reload extends BaseTransphpormRenderer {

		public const STATUS_CODE = 205; // Reset Content

		protected PreviousResponse $previousResponse;

		public function __construct(protected string $handler) {

			$this->setHeaders(self::STATUS_CODE, [

				PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
			]);
		}

		public function setPreviousResponse (PreviousResponse $previousResponse):void {

			$this->previousResponse = $previousResponse;
		}

		public function render ():string {

			return $this->previousResponse

			->invokeRenderer($this->rawResponse)

			->render();
		}
	}
?>