<?php

namespace Suphle\Testing\Proxies;

use Suphle\Hydration\Container;

use Suphle\Contracts\Routing\MiddlewareRegistry;

use Suphle\Request\PayloadStorage;

use Suphle\Routing\NamedRouteReader;

use Suphle\Testing\Condiments\DirectHttpTest;

use Suphle\Testing\Proxies\Extensions\{TestResponseBridge, MiddlewareManipulator};

use Suphle\Exception\Explosives\NotFoundException;

trait ModuleHttpTest
{
    use DirectHttpTest, ExaminesHttpResponse;

    private array $staticHeaders = [];

    private MiddlewareRegistry $mockMiddlewareRegistry;

    protected function setUp ():void {

        $this->mockMiddlewareRegistry = $this->getContainer()->getClass(MiddlewareRegistry::class);
    }

    protected function expandUrl (
        string $coordinatorClass, string $handlingMethod, array $parameters,
        bool $getMirror
    ):string {

        return $this->getContainer()->getClass(NamedRouteReader::class)

        ->expressUrl($coordinatorClass, $handlingMethod, $parameters, $getMirror);
    }

    public function withHeaders(array $headers): self
    {

        $this->staticHeaders = array_merge($this->staticHeaders, $headers);

        return $this;
    }

    public function withToken(string $token, string $type = "Bearer"): self
    {

        $this->staticHeaders["Authorization"] = $type . " " . $token;

        return $this;
    }

    /**
     * Assumes there's some behavior this middleware may have that we aren't comfortable triggering
     *
     * @param {handlers}:Middleware::class[]. Don't specify any argument to disable all
    */
    public function withoutMiddleware(array $handlers = []): self
    {

        if (empty($handlers)) {

            $this->mockMiddlewareRegistry->disableAll();
        } else {
            $this->mockMiddlewareRegistry->disableCollectors($handlers);
        }

        return $this;
    }

    /**
     * Useful when we want to see the implication of using a particular middleware, in test
     *
     * @param {handlers}:Middleware[]
    */
    public function withMiddleware(array $handlers): self
    {

        $this->mockMiddlewareRegistry->addToActiveStack($handlers);

        return $this;
    }

    protected function assertUsedMiddleware(array $handlers): void
    {
        $notUsed = $this->mockMiddlewareRegistry->getNotUsed($handlers);

        $this->assertEmpty(
            $notUsed,
            "Failed to assert that given handlers were all used. Only matched: ".

            json_encode($notUsed, JSON_PRETTY_PRINT)
        );
    }

    protected function assertDidntUseMiddleware(array $handlers): void
    {

        $notUsed = $this->mockMiddlewareRegistry->getNotUsed($handlers);

        $intersectingUsed = array_intersect($handlers, $notUsed);

        $this->assertEmpty(
            $intersectingUsed,
            "Didn't expect to use the following handlers: ".

            json_encode($intersectingUsed, JSON_PRETTY_PRINT)
        );
    }

    public function get(string $url, array $payload = [], array $headers = []): TestResponseBridge
    {

        return $this->gatewayResponse($url, __FUNCTION__, $payload, $headers);
    }

    public function getJson(string $url, array $payload = [], array $headers = []): TestResponseBridge
    {

        return $this->json("get", $url, $payload, $headers);
    }

    private function gatewayResponse(
        string $requestPath,
        string $httpMethod,
        ?array $payload,
        array $headers,
        array $files = []
    ): TestResponseBridge {

        $entrance = $this->entrance;

        $this->setHttpParams($requestPath, $httpMethod, $payload, $headers);

        $this->provideFileObjects($files, $httpMethod);

        $entrance->diffuseSetResponse(false); // Writing anything to the real headers is redundant in test environment

        $renderer = $entrance->underlyingRenderer();

        return $this->makeExaminable($renderer);
    }

    public function post(
        string $url,
        array $payload = [],
        array $headers = [],
        array $files = []
    ): TestResponseBridge {

        return $this->gatewayResponse(
            $url,
            __FUNCTION__,
            $payload,
            $headers,
            $files
        );
    }

    public function postJson(
        string $url,
        array $payload = [],
        array $headers = [],
        array $files = []
    ): TestResponseBridge {

        return $this->json("post", $url, $payload, $headers, $files);
    }

    public function put(
        string $url,
        array $payload = [],
        array $headers = [],
        array $files = []
    ): TestResponseBridge {

        return $this->gatewayResponse(
            $url,
            __FUNCTION__,
            $payload,
            $headers,
            $files
        );
    }

    public function putJson(
        string $url,
        array $payload = [],
        array $headers = [],
        array $files = []
    ): TestResponseBridge {

        return $this->json("put", $url, $payload, $headers, $files);
    }

    public function delete(string $url, array $payload = [], array $headers = []): TestResponseBridge
    {

        return $this->gatewayResponse($url, __FUNCTION__, $payload, $headers);
    }

    public function deleteJson(string $url, array $payload = [], array $headers = []): TestResponseBridge
    {

        return $this->json("delete", $url, $payload, $headers);
    }

    public function json(
        string $httpMethod,
        string $url,
        array $payload = [],
        array $headers = [],
        array $files = []
    ): TestResponseBridge {

        $converted = json_encode($payload, JSON_THROW_ON_ERROR);

        $newHeaders = array_merge([
            "Content-Length" => mb_strlen($converted, "8bit"),

            PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE,

            PayloadStorage::ACCEPTS_KEY => PayloadStorage::JSON_HEADER_VALUE
        ], $headers);

        return $this->gatewayResponse(
            $url,
            $httpMethod,
            $payload,
            $newHeaders,
            $files
        );
    }
}
