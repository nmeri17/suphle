<?php
namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\BaseRenderer;

abstract class GenericRenderer implements BaseRenderer
{
    protected int $statusCode;

    protected array $headers = [];

    protected iterable $rawResponse = [];

    protected bool $shouldDeferValidationFailure = true;

    protected function renderJson(): string
    {

        return json_encode($this->rawResponse, JSON_THROW_ON_ERROR);
    }

    public function setRawResponse(iterable $response): BaseRenderer
    {
        $this->rawResponse = $response;

        return $this;
    }

    public function getRawResponse(): iterable
    {

        return $this->rawResponse;
    }

    public function setHeaders(int $statusCode, array $headers): void
    {

        $this->statusCode = $statusCode;

        $this->headers = array_merge($this->headers, $headers);
    }

    public function getStatusCode(): int
    {

        return $this->statusCode;
    }

    public function getHeaders(): array
    {

        return $this->headers;
    }

    public function deferValidationContent(): bool
    {

        return $this->shouldDeferValidationFailure;
    }

    /**
    * Insurance against routes that can possibly fail validation that don't return an array
    */
    public function forceArrayShape(array $includeData = []): void
    {

        $currentBody = $this->rawResponse;

        if (!is_array($currentBody)) {

            $currentBody = json_decode(
                json_encode($currentBody, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        $this->rawResponse = array_merge($currentBody, $includeData);
    }
}
