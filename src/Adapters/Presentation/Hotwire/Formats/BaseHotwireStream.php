<?php
namespace Suphle\Adapters\Presentation\Hotwire\Formats;

use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};
use Suphle\Contracts\Presentation\MirrorableRenderer;
use Suphle\Request\PayloadStorage;

use Suphle\Response\Format\BaseHtmlRenderer;

use Suphle\Services\{BaseCoordinator, Decorators\VariableDependencies};

use Suphle\Adapters\Presentation\Hotwire\HotwireStreamBuilder;

#[VariableDependencies([

    "setPayloadStorage"
])]
abstract class BaseHotwireStream extends BaseHtmlRenderer implements MirrorableRenderer
{

    public const TURBO_INDICATOR = "text/vnd.turbo-stream.html",

    APPEND_ACTION = "append", PREPEND_ACTION = "prepend",

    BEFORE_ACTION = "before", AFTER_ACTION = "after",

    REPLACE_ACTION = "replace", UPDATE_ACTION = "update",

    REMOVE_ACTION = "remove";

    protected array $hotwireHandlers = [], // details about each handler being bound
    $streamBuilders = [], // houses each node and its corresponding parsed content
    $streams = [];

    protected PayloadStorage $payloadStorage;

    protected BaseRenderer $fallbackRenderer;

    protected string $markupName;

    protected int $statusCode = 200;

    public function setPayloadStorage(PayloadStorage $payloadStorage): void
    {
        $this->payloadStorage = $payloadStorage;
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

    public function setWantsJson(): void
    {
        if ($this->fallbackRenderer instanceof MirrorableRenderer) {

            $this->fallbackRenderer->setWantsJson();
        }
    }

    public function addAppend(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [ // keying by action will limit each renderer to one action type

            self::APPEND_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addPrepend(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::PREPEND_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addReplace(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::REPLACE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addUpdate(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::UPDATE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addBefore(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::BEFORE_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addAfter(iterable $data, callable $target, string $markupName): self
    {

        $this->hotwireHandlers[] = [

            self::AFTER_ACTION, ...func_get_args()
        ];

        return $this;
    }

    public function addRemove(iterable $data, callable $target): self
    {

        $this->hotwireHandlers[] = [self::REMOVE_ACTION, $data, $target];

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

        $nodeResponses = [];

        foreach ($this->hotwireHandlers as [$hotwireAction, $dataSource, $genTarget, $uiPartial ]) {

            $nodeResponses[] = $result = $this->rawResponse = $dataSource; // htmlParser requires active data to be set on rawResponse;

            $uiTarget = $genTarget($result);

            $builder = new HotwireStreamBuilder($hotwireAction, $uiTarget);

            $builder->wrapContent($this->parseNodeContent($uiPartial));

            $this->streamBuilders[] = $builder; // strictly for testing

            $allStreams .= $builder->getTurboTags();
        }
        $this->rawResponse = $nodeResponses;

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

    public function getStreamBuilders(): array
    {

        return $this->streamBuilders;
    }

    public function getHotwireHandlers(): array
    {

        return $this->hotwireHandlers;
    }

    public function addStream(string $action, string $target, string $template, array $data = []): void
    {
        $this->streams[] = [
            "action" => $action,
            "target" => $target,
            "template" => $template,
            "data" => $data
        ];
    }
}
