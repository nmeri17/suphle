<?php

namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

use Suphle\Response\Format\BaseHtmlRenderer;

use Suphle\Request\PayloadStorage;

use Suphle\Hydration\Structures\CallbackDetails;

use Suphle\Services\{ServiceCoordinator, Decorators\VariableDependencies};

use Suphle\Adapters\Presentation\Hotwire\HotwireStreamBuilder;

#[VariableDependencies([

    "setPayloadStorage", "setCallbackDetails"
])]
abstract class BaseHotwireStream extends BaseHtmlRenderer
{
    public const TURBO_INDICATOR = "text/vnd.turbo-stream.html",

    APPEND_ACTION = "append", PREPEND_ACTION = "prepend",

    BEFORE_ACTION = "before", AFTER_ACTION = "after",

    REPLACE_ACTION = "replace", UPDATE_ACTION = "update",

    REMOVE_ACTION = "remove";

    protected array $hotwireHandlers = [];
    protected array // details about each handler being bound

    $nodeResponses = [];
    protected array // result of executing each handler

    $streamBuilders = []; // houses each node and its corresponding parsed content

    protected PayloadStorage $payloadStorage;

    protected BaseRenderer $fallbackRenderer;

    protected string $markupName;

    protected CallbackDetails $callbackDetails;

    protected int $statusCode = 200;

    protected bool $trimmedActions = false;

    public function setPayloadStorage(PayloadStorage $payloadStorage): void
    {

        $this->payloadStorage = $payloadStorage;
    }

    public function setCallbackDetails(CallbackDetails $callbackDetails): void
    {

        $this->callbackDetails = $callbackDetails;
    }

    public function setSession(Session $sessionClient): void
    {
	
		parent::setSession($sessionClient);

        $this->fallbackRenderer->setSession($sessionClient);
    }

    public function isHotwireRequest(): bool
    {

        return $this->payloadStorage->matchesHeader(
            PayloadStorage::ACCEPTS_KEY,
            self::TURBO_INDICATOR
        );
    }

    public function addAppend(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [ // keying by action will limit each renderer to one action type

            self::APPEND_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addPrepend(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::PREPEND_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addReplace(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::REPLACE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addUpdate(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::UPDATE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addBefore(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::BEFORE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addAfter(string $handler, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::AFTER_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addRemove(string $handler, callable $target): self
    {

        $this->hotwireHandlers[] = [self::REMOVE_ACTION, $handler, $target];

        return $this;
    }

    public function invokeActionHandler(array $handlerParameters): BaseRenderer
    {

        if (!$this->isHotwireRequest()) {

            $this->fallbackRenderer->invokeActionHandler($handlerParameters);
        } else {
            foreach ($this->hotwireHandlers as $index => [, $handler]) {

                $this->nodeResponses[] = call_user_func_array(
                    [$this->coordinator, $handler],
                    $handlerParameters[$index]
                );
            }
        }

        return $this;
    }

    public function render(): string
    {

        $useFallback = !$this->isHotwireRequest();

        if ($useFallback) {

            $renderedContent = $this->fallbackRenderer->render();
        }

        $this->setConditionalHeader($useFallback); // this has to be called after rendering occurs due to the fact that some headers (e.g. redirect) are only known after render is called

        if ($useFallback) {
            return $renderedContent;
        }

        $allStreams = "";

        foreach ($this->hotwireHandlers as $index => $handlerDetails) {

            [$hotwireAction,, $targets ] = $handlerDetails;

            $this->rawResponse = $this->nodeResponses[$index]; // for use by the target derivator and the html parser at each node

            $targetString = $this->callbackDetails

            ->recursiveValueDerivation($targets, $this);

            $builder = new HotwireStreamBuilder($hotwireAction, $targetString);

            $builder->wrapContent(
                $this->parseNodeContent(@$handlerDetails[3])
            );

            $this->streamBuilders[] = $builder;

            $allStreams .= $builder;
        }

        $this->rawResponse = $this->nodeResponses; // reset it

        return $allStreams;
    }

    protected function setConditionalHeader(bool $notHot): void
    {

        if ($notHot) {

            $this->statusCode = $this->fallbackRenderer->getStatusCode();

            $this->headers = $this->fallbackRenderer->getHeaders();
        } else {
            $this->setHeaders($this->statusCode, [

                PayloadStorage::CONTENT_TYPE_KEY => self::TURBO_INDICATOR
            ]);
        }
    }

    protected function parseNodeContent(?string $markupName = null): string
    {

        if (is_null($markupName)) {
            return "";
        } // "remove" action has no markup

        $this->markupName = $markupName;

        return $this->htmlParser->parseRenderer($this);
    }

    /**
     * These methods expect the partials to check the PayloadStorage for presence of data from previous request
    */
    public function retainCreateNodes(): self
    {

        return $this->trimUnwantedActions([self::REPLACE_ACTION]);
    }

    public function retainUpdateNodes(): self
    {

        return $this->trimUnwantedActions([self::UPDATE_ACTION]);
    }

    protected function trimUnwantedActions(array $permittedActions): self
    {

        $handlersCopy = $this->hotwireHandlers;

        $this->trimmedActions = true;

        foreach ($handlersCopy as $index => [$hotwireAction]) {

            if (!in_array($hotwireAction, $permittedActions)) {

                unset($handlersCopy[$index]);
            }
        }

        if (!empty($handlersCopy)) {

            $this->hotwireHandlers = array_values($handlersCopy);
        }

        return $this;
    }

    public function getStreamBuilders(): array
    {

        return $this->streamBuilders;
    }

    public function getHotwireHandlers(): array
    {

        return $this->hotwireHandlers;
    }

    public function setCoordinatorClass(ServiceCoordinator $coordinator): void
    {

        parent::setCoordinatorClass($coordinator);

        $this->fallbackRenderer->setCoordinatorClass($coordinator);
    }

    public function setRawResponse(iterable $response): BaseRenderer
    {

        $this->forceArrayShape($response);

        if ($this->trimmedActions) {

            /**
             * Wrap in extra array to match nodeResponse structure ([[], []...]).
             *
             * Since no action handler will be called in the eventuality of a validation failure, set this for all nodes found.
             *
             * Can either be one (on trim success), or all otherwise
            */
            foreach ($this->hotwireHandlers as $handlerDetails) {

                $this->nodeResponses[] = $this->rawResponse;
            }
        } else {
            $this->nodeResponses = $this->rawResponse;
        }

        return $this;
    }
}
