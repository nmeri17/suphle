<?php
namespace Suphle\Response\Format;

use Suphle\Contracts\Presentation\BaseRenderer;

abstract class GenericRenderer implements BaseRenderer
{
    protected int $statusCode;

    protected array $headers = [];

    protected iterable $rawResponse = [];

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
}
