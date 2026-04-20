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
    protected ?string $permanentPath = null,
    
    $httpMethod = null;

    protected array $queryParameters = [];

    protected static ?ServerRequestInterface $contextualRequest = null;

    protected ?string $canaryState = null;

    public function __construct(protected readonly Router $routeConfig)
    {

        //
    }

    public static function setLoopInput (?ServerRequestInterface $contextualRequest):void {

        self::$contextualRequest = $contextualRequest;
    }

    public function getContextualRequest ():?ServerRequestInterface {

        return self::$contextualRequest;
    }

    public function getPath(): ?string
    {

        return $this->permanentPath;
    }

    public function setPath(string $requestPath): void
    {

        $this->permanentPath = $requestPath;
    }

    public function setQueries(array $queryParameters): void
    {

        $this->queryParameters = $queryParameters;
    }

    public function getQueryParameters(): array
    {

        return $this->queryParameters;
    }

    public static function fromModules(array $descriptors, string $requestPath, ?string $forceHttpMethod): void
    {

        foreach ($descriptors as $descriptor) {

            static::fromContainer(
                $descriptor->getContainer(),
                $requestPath,
                $forceHttpMethod
            );
        }
    }

    public static function fromContainer(Container $container, string $requestPath, ?string $forceHttpMethod): ?RequestDetails
    {

        $components = parse_url($requestPath);

        $pathComponent = @$components["path"];

        if (is_null($pathComponent)) {
            return null;
        }

        $instance = static::newRequestInstance($container); // using static instead of self so any sub-class (e.g. a mock) will be called

        $instance->setPath($pathComponent);

        parse_str($components["query"] ?? "", $queryParameters);

        $instance->setQueries($queryParameters);

        if (!is_null($forceHttpMethod)) {

            $instance->setHttpMethod($forceHttpMethod);
        } else {
            $instance->deriveHttpMethod();
        }

        return $instance;
    }

    public static function newRequestInstance(Container $container): RequestDetails
    {

        $selfName = self::class;

        $container->refreshClass($selfName);

        return $container->getClass($selfName); // automatically binds it
    }

    protected function setHttpMethod(string $method): void
    {

        $this->httpMethod = $method;
    }

    protected function deriveHttpMethod(): void
    {

        $hiddenField = "_method";

        $postPayload = self::$contextualRequest->getParsedBody() ?? [];

        $serverPayload = self::$contextualRequest->getServerParams();

        if (array_key_exists($hiddenField, $postPayload)) {

            $methodName = $postPayload[$hiddenField];
        } elseif (array_key_exists("REQUEST_METHOD", $serverPayload)) {

            $methodName = $serverPayload["REQUEST_METHOD"];
        } else {
            $methodName = "get";
        }

        $this->httpMethod = strtolower((string) $methodName);
    }

    public function getHttpMethod(): ?string
    {

        return $this->httpMethod;
    }

    public function matchesMethod(string $method): bool
    {

        return preg_match("/" . $this->httpMethod . "/i", $method);
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
        return $this->routeConfig->matchesApi($this->permanentPath);
    }
}
