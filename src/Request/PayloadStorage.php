<?php
namespace Suphle\Request;

use Suphle\Services\Decorators\BindsAsSingleton;

use GuzzleHttp\Psr7\ServerRequest; // the nyholm equivalent is not extendable

use Psr\Http\Message\ServerRequestInterface;

#[BindsAsSingleton]
class PayloadStorage extends ServerRequest {

    public const JSON_HEADER_VALUE = "application/json",

    HTML_HEADER_VALUE = "text/html",

    CONTENT_TYPE_KEY = "Content-Type",

    ACCEPTS_KEY = "Accept", LOCATION_KEY = "Location",

    ON_REFRESH = "new_request";

    protected array $upserts = [];

    public function __construct(
        protected readonly RequestDetails $requestDetails
    ) { }

    protected function getPsrOrigin():?ServerRequestInterface {

        return $this->requestDetails->getContextualRequest();
    }

    public function getCookieParams():array {

        return $this->getPsrOrigin()?->getCookieParams();
    }

    public function fullPayload():array
    {
        if (!$psrOrigin = $this->getPsrOrigin()) return [];

        if ($this->requestDetails->isGetRequest())

            $data = $psrOrigin->getQueryParams();

        $data = $psrOrigin->getParsedBody();

        return array_merge($data, $this->upserts);
    }

    public function setUpserts(array $upserts): void
    {

        $this->upserts = $upserts;
    }

    public function acceptsJson(): bool
    {

        return $this->matchesHeader(self::ACCEPTS_KEY, self::JSON_HEADER_VALUE);
    }

    public function matchesHeader(string $name, string $expectedValue): bool
    {
        $psrOrigin = $this->getPsrOrigin();

        if (!$psrOrigin?->hasHeader($name)) {
            return false;
        }

        $currentValue = str_replace("/", "\/", $psrOrigin->getHeaderLine($name));

        return preg_match("/^$currentValue$/i", $expectedValue);
    }

    public function hasKey(string $property): bool
    {

        return array_key_exists($property, $this->fullPayload());
    }

    public function keyHasContent(string $property): bool
    {

        return $this->hasKey($property) &&

        !empty($this->getKey($property));
    }

    public function getKey(string $property)
    {

        return $this->fullPayload()[$property];
    }

    public function matchesContent(string $property, $expectedValue): bool
    {

        return $this->keyHasContent($property) &&

        $this->getKey($property) == $expectedValue;
    }

    public function getKeyForPositiveInt(string $key): int
    {

        return $this->positiveIntValue($this->fullPayload()[$key]);
    }

    public function only(array $include): array
    {

        return array_filter($this->fullPayload(), fn ($key) => in_array($key, $include), ARRAY_FILTER_USE_KEY);
    }

    public function except(array $exclude): array
    {

        return array_filter($this->fullPayload(), fn ($key) => !in_array($key, $exclude), ARRAY_FILTER_USE_KEY);
    }
}
