<?php

namespace Suphle\Response\Format;

use Suphle\Request\PayloadStorage;

/*
 * Should not be used in conjuction with form submissions. Form actions should leave the request's originator
*/
class Markup extends BaseHtmlRenderer {
    public function __construct(
        public readonly string $markupName,

        public iterable $rawResponse = []
    ) {
        $this->setHeaders(self::STATUS_CODE, [
            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
        ]);
    }

    public function render(): string
    {
        $routeInfo = $this->requestDetails->getComputedDetails();

        if (is_null($routeInfo) || !$routeInfo->isMirror) { // 404?

            return $this->htmlParser->parseRenderer($this);
        }

        $this->setHeaders($this->statusCode, $routeInfo->mirrorHeader);

        return $this->renderJson();
    }
}
