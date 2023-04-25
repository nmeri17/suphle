<?php

namespace Suphle\Testing\Condiments;

use Suphle\Contracts\{Requests\FileInputReader, Auth\UserHydrator};

use Suphle\Request\{RequestDetails, PayloadStorage};

use Suphle\Testing\Proxies\Extensions\InjectedUploadedFiles;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DirectHttpTest
{
    use MockFacilitator;

    private string $HTML_HEADER_VALUE = "application/x-www-form-urlencoded";

    private array $jsonHeaders = [

        PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
    ];

    /**
     * Writes to the superglobals RequestDetails can read from but doesn't actually send any request. Use when we're invoking router/request handler directly
    */
    protected function setHttpParams(string $requestPath, string $httpMethod = "get", ?array $payload = [], array $headers = []): void
    {

        $this->setRequestPath($requestPath, $httpMethod);

        $payloadStorage = $this->getContainer()->getClass(PayloadStorage::class);

        $payloadStorage->setFullPayload($payload);

        foreach ($headers as $key => $value) {

            $payloadStorage = $payloadStorage->withHeader($key, $value);
        }
        $this->massProvide([

            PayloadStorage::class => $payloadStorage
        ]);
    }

    /**
     * @param {files} UploadedFile[]
    */
    protected function provideFileObjects(array $files, string $httpMethod): void
    {

        if (!$this->isValidPayloadType($httpMethod)) {
            return;
        }

        $this->massProvide([

            FileInputReader::class => new InjectedUploadedFiles($files)
        ]);
    }

    abstract protected function setRequestPath(string $requestPath, string $httpMethod): void;

    protected function setJsonParams(string $requestPath, array $payload, string $httpMethod = "post"): bool
    {

        if ($this->isValidPayloadType($httpMethod)) {

            $this->setHttpParams($requestPath, $httpMethod, $payload, $this->jsonHeaders);

            return true;
        }

        return false;
    }

    protected function setHtmlForm(string $requestPath, array $payload, string $httpMethod = "post"): bool
    {

        $headers = [

            PayloadStorage::CONTENT_TYPE_KEY => $this->HTML_HEADER_VALUE
        ];

        if ($this->isValidPayloadType($httpMethod)) {

            $this->setHttpParams($requestPath, $httpMethod, $payload, $headers);

            return true;
        }

        return false;
    }

    private function isValidPayloadType(string $httpMethod): bool
    {

        return in_array($httpMethod, ["post", "put", "delete"]);
    }
}
