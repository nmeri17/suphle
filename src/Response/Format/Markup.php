<?php

namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\MirrorableRenderer;
use Suphle\Request\PayloadStorage;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

/*
 * Should not be used in conjuction with form submissions. Form actions should leave the request's originator
*/
class Markup extends BaseHtmlRenderer implements MirrorableRenderer, OpenApiRenderer
{
    use OpenApiRendererTrait;

    protected bool $wantsJson = false;

    public function __construct(
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

    /**
     * Override default content type for Markup
     */
    public static function getContentType(): string
    {
        return PayloadStorage::HTML_HEADER_VALUE;
    }

    /**
     * Override default response schema for Markup
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'format' => 'html',
            'description' => static::getDescription()
        ];
    }

    /**
     * Override default description for Markup
     */
    public static function getDescription(): string
    {
        return 'HTML markup response';
    }
}
