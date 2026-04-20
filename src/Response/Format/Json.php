<?php

namespace Suphle\Response\Format;

use Suphle\Request\PayloadStorage;

class Json extends GenericRenderer
{

    public const STATUS_CODE = 200;

    protected bool $shouldDeferValidationFailure = false;

    protected int $statusCode = self::STATUS_CODE;

    public function __construct(array $data)
    {
        $this->setRawResponse($data);

        $this->setHeaders(self::STATUS_CODE, [
            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
        ]);
    }

    public function render(): string
    {
        return $this->renderJson();
    }

    /**
     * {@inheritdoc}
     */
    public function deferValidationContent(): bool
    {
        return false;
    }
}
