<?php

namespace Suphle\Testing\Proxies;

use Suphle\Hydration\Container;

use Suphle\Middleware\MiddlewareRegistry;

use Suphle\Request\PayloadStorage;

use Suphle\Testing\Condiments\DirectHttpTest;

use Suphle\Testing\Proxies\Extensions\{TestResponseBridge, MiddlewareManipulator};

use Suphle\Exception\Explosives\NotFoundException;

trait ModuleHttpTest
{
    use DirectHttpTest;
    use ExaminesHttpResponse;

    private array $staticHeaders = [];

    private ?MiddlewareRegistry $mockMiddlewareRegistry = null;

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
     * @param {collectorNames} CollectionMetaFunnel::class[]
    */
    public function withoutMiddleware(array $collectorNames = []): self
    {

        $this->setMiddlewareRegistry();

        if (empty($collectors)) {

            $this->mockMiddlewareRegistry->disableAll();
        } else {
            $this->mockMiddlewareRegistry->disableCollectors($collectors);
        }

        return $this;
    }

    /**
     * Useful when we want to see the implication of using a particular middleware, in test
     *
     * @param {collectors} CollectionMetaFunnel[]
    */
    public function withMiddleware(array $collectors): self
    {

        $this->setMiddlewareRegistry();

        $this->mockMiddlewareRegistry->addToActiveStack($collectors);

        return $this;
    }

    private function setMiddlewareRegistry(): void
    {

        if (is_null($this->mockMiddlewareRegistry)) {

            $this->mockMiddlewareRegistry = $this->getContainer()->getClass(MiddlewareManipulator::class);

            $this->massProvide([

                MiddlewareRegistry::class => $this->mockMiddlewareRegistry
            ]);
        }
    }

    protected function assertUsedCollectorNames(array $collectorNames): void
    {

        $matches = $this->matchingCollectorNames($collectorNames);

        $this->assertSame(
            $matches,
            $collectorNames,
            "Failed to assert that given collectors were all used. Only matched: ".

            json_encode($matches, JSON_PRETTY_PRINT)
        );
    }

    protected function assertUsedCollectors(array $collectors): void
    {

        $unused = array_diff($collectors, $this->getAllCollectors());

        $this->assertEmpty(
            $unused,
            "Failed to assert that collectors ".

            json_encode($unused, JSON_PRETTY_PRINT). " were used"
        );
    }

    protected function assertDidntUseCollectorNames(array $collectorNames): void
    {

        $matches = $this->matchingCollectorNames($collectorNames);

        $intersectingUsed = array_intersect($collectorNames, $matches);

        $this->assertEmpty(
            $matches,
            "Didn't expect to use the following collectors: ".

            json_encode($intersectingUsed, JSON_PRETTY_PRINT)
        );
    }

    protected function assertDidntUseCollectors(array $collectors): void
    {

        $intersectingUsed = array_intersect($collectors, $this->getAllCollectors());

        $this->assertEmpty(
            $intersectingUsed,
            "Didn't expect to use collectors " .

            json_encode($intersectingUsed, JSON_PRETTY_PRINT)
        );
    }

    private function getAllCollectors(): array
    {

        return $this->entrance->getActiveContainer()

        ->getClass(MiddlewareRegistry::class)->getRoutedFunnels();
    }

    /**
     * @return Array of matching collector names
    */
    private function matchingCollectorNames(array $collectorNames): array
    {

        $allCollectors = array_map(
            fn ($collector) => $collector::class,
            $this->getAllCollectors()
        );

        return array_intersect($collectorNames, $allCollectors);
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
