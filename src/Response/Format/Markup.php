<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\MirrorableRenderer;

use Suphle\Request\PayloadStorage;

/*
 * Should not be used in conjuction with form submissions. Form actions should leave the request's originator
*/
class Markup extends BaseHtmlRenderer implements MirrorableRenderer
{
    protected bool $wantsJson = false;

    public function __construct(
        protected string $handler,
        protected string $markupName
    ) {

        $this->setHeaders(200, [

            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
        ]);
    }

    public function render(): string
    {

        if (!$this->wantsJson) {

            return $this->htmlParser->parseRenderer($this);
        }

        $this->setHeaders(200, [

            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
        ]);

        return $this->renderJson();
    }

    public function setWantsJson(): void
    {

        $this->wantsJson = true;

        $this->shouldDeferValidationFailure = false;
    }
}
