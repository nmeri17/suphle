<?php
namespace Suphle\Request;

use Suphle\Contracts\Config\Router;

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Hydration\Container;

use Psr\Http\Message\ServerRequestInterface;

use InvalidArgumentException;

#[BindsAsSingleton]
class RequestDetails
{
    protected string $requestPath;

    protected ?string $forceHttpMethod = null;

    protected ?ServerRequestInterface $contextualRequest = null;

    protected ?string $canaryState = null; // cfr

    public function __construct(protected readonly Router $routeConfig)
    { }

    public function detailsToCompute(
        string $requestPath, ?string $forceHttpMethod,
        ?ServerRequestInterface $contextualRequest
    ):void {
        $this->requestPath = $requestPath;

        $this->forceHttpMethod = $forceHttpMethod;

        $this->contextualRequest = $contextualRequest;
    }

    public function getPath(): ?string
    {
        $components = parse_url($this->requestPath);

        return @$components["path"];
    }

    public function getQueryParameters(): array
    {
        $components = parse_url($this->requestPath);

        parse_str($components["query"] ?? "", $queryParameters);

        return $queryParameters;
    }

    public function getHttpMethod():string
    {
        if (!is_null($this->forceHttpMethod)) return $this->forceHttpMethod;

        return $this->deriveHttpMethod();
    }

    protected function deriveHttpMethod():string {

        $methodInPayload = "_method";

        $methodInHeader = "REQUEST_METHOD";

        $postPayload = $this->contextualRequest->getParsedBody() ?? [];

        $serverPayload = $this->contextualRequest->getServerParams();

        if (array_key_exists($methodInPayload, $postPayload)) {

            $methodName = $postPayload[$methodInPayload];
        } elseif (array_key_exists($methodInHeader, $serverPayload)) {

            $methodName = $serverPayload[$methodInHeader];
        } else {
            $methodName = "get";
        }

        return strtolower((string) $methodName);
    }

    public function matchesMethod(string $method): bool
    {

        return strtolower($method) == $this->getHttpMethod();
    }

    public function isGetRequest(): bool
    {

        return $this->matchesMethod("get");
    }

    public function isPostRequest(): bool
    {

        return $this->matchesMethod("post");
    }

    public function setCanaryState(?string $state): void
    {

        $this->canaryState = $state;
    }

    public function getCanaryState(): ?string
    {

        return $this->canaryState;
    }

    public function isApiRoute():bool { // not reliable checking this from SessionStorage cuz what if it's an unprotected route or not found
        return $this->routeConfig->matchesApi($this->getPath());
    }

    public function getContextualRequest ():?ServerRequestInterface {

        return $this->contextualRequest;
    }
}
