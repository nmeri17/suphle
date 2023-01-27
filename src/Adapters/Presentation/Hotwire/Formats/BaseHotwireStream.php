<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Request\PayloadStorage;

	use Suphle\Hydration\Structures\CallbackDetails;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Adapters\Presentation\Hotwire\HotwireStreamBuilder;

	#[VariableDependencies([

		"setPayloadStorage", "setCallbackDetails"
	])]
	abstract class BaseHotwireStream extends BaseTransphpormRenderer {

		public const TURBO_INDICATOR = "text/vnd.turbo-stream.html",

		APPEND_ACTION = "append", PREPEND_ACTION = "prepend",

		BEFORE_ACTION = "before", AFTER_ACTION = "after",

		REPLACE_ACTION = "replace", UPDATE_ACTION = "update";

		protected array $hotwireHandlers = [], $nodeResponses = [],

		$streamBuilders = [];

		protected PayloadStorage $payloadStorage;

		protected BaseRenderer $fallbackRenderer;

		protected string $markupName;

		protected ?string $templateName;

		protected CallbackDetails $callbackDetails;

		public function setPayloadStorage (PayloadStorage $payloadStorage):void {

			$this->payloadStorage = $payloadStorage;
		}

		public function setCallbackDetails (CallbackDetails $callbackDetails):void {

			$this->callbackDetails = $callbackDetails;
		}

		public function isHotwireRequest ():bool {

			return $this->payloadStorage->matchesHeader(

				PayloadStorage::ACCEPTS_KEY, self::TURBO_INDICATOR
			);
		}

		public function addAppend (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::APPEND_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addPrepend (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::PREPEND_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addReplace (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::REPLACE_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addUpdate (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::UPDATE_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addBefore (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::BEFORE_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addAfter (string $handler, callable $target, string $markupName, string $templateName = null):self {

			$this->hotwireHandlers[] = [

				self::AFTER_ACTION, ...func_get_args()
			];

			return $this;
		}

		public function addRemove (string $handler, callable $target):self {

			$this->hotwireHandlers[] = ["remove", $handler];

			return $this;
		}

		public function invokeActionHandler (array $handlerParameters):BaseRenderer {

			if (!$this->isHotwireRequest())

				$this->fallbackRenderer->invokeActionHandler($handlerParameters);

			else {

				$coordinator = $this->getCoordinator();

				foreach ($this->hotwireHandlers as [, $handler]) {

					$this->nodeResponses[] = call_user_func_array(

						[$coordinator, $handler], $handlerParameters
					);
				}
			}

			return $this;
		}

		public function render ():string { // we can't set those stuff for each route so renderers may have to be refactored to be internally sent to and fro the container before [render] is called

			$this->setConditionalHeader();

			if (!$this->isHotwireRequest())

				return $this->fallbackRenderer->render();

			$allStreams = "";

			foreach ($this->hotwireHandlers as $index => [

				$hotwireAction,, $targets, $markupName, $templateName
			]) {

				$targetString = $this->callbackDetails->recursiveValueDerivation($targets);

				$builder = new HotwireStreamBuilder($hotwireAction, $targetString);

				$builder->wrapContent($this->parseNodeContent(

					$markupName, $templateName,

					$this->nodeResponses[$index]
				));

				$this->streamBuilders[] = $builder;

				$allStreams .= $builder;
			}

			return $allStreams;
		}

		protected function setConditionalHeader ():void {

			if ($this->isHotwireRequest())

				$this->setHeaders(200, [
				
					PayloadStorage::CONTENT_TYPE_KEY => self::TURBO_INDICATOR
				]);

			else {

				$this->statusCode = $this->fallbackRenderer->getStatusCode();

				$this->headers = $this->fallbackRenderer->getHeaders();
			}
		}

		protected function parseNodeContent (?string $markupName, ?string $templateName, $rawResponse):string {

			if (is_null($markupName)) return ""; // "remove" action has no markup

			foreach ([$markupName, $templateName, $rawResponse] as $property)

				$this->$$property = $property;

			return $this->htmlParser->parseAll($this);
		}

		/**
		 * These methods expect the partials/action handlers to check the PayloadStorage for presence of data from previous request
		*/
		public function retainCreateNodes ():self {

			return $this->trimUnwantedActions([self::REPLACE_ACTION]);
		}

		public function retainUpdateNodes ():self {

			return $this->trimUnwantedActions([self::UPDATE_ACTION]);
		}

		protected function trimUnwantedActions (array $permittedActions):self {

			$handlersCopy = $this->hotwireHandlers;

			foreach ($handlersCopy as $index => [$hotwireAction]) {

				if (!in_array($hotwireAction, $permittedActions))

					unset($handlersCopy[$index]);
			}

			if (!empty($handlersCopy))

				$this->hotwireHandlers = $handlersCopy;
			
			return $this;
		}

		public function isSerializable ():bool {

			return false;
		}

		public function getStreamBuilders ():array {

			return $this->streamBuilders;
		}
	}
?>