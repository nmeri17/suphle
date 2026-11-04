<?php

namespace Suphle\Tests\Integration\Services;

use Suphle\Hydration\Container;

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Request\{PayloadStorage, RequestDetails};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

trait ReplacesRequestPayload
{
    protected function getModules(): array
    {

        return [new ModuleOneDescriptor(new Container())];
    }

    // using this instead of filling request object directly since no routing occurs. we just call the services directly -- just a shortcut to creating routes
    protected function stubRequestObjects(int $segmentValue, array $payload = [], array $requestStubs = []): void
    {

        $payloadStorage = $this->positiveDouble(PayloadStorage::class);

        $payloadStorage->setUpserts($payload);

        $requestObjects = [

            RouteInfo::class => $this->positiveDouble(RouteInfo::class, [

                "getSegmentValue" => $segmentValue
            ]),
            PayloadStorage::class => $payloadStorage
        ];

        if (!empty($requestStubs)) {

            $requestObjects[RequestDetails::class] = $this->positiveDouble(RequestDetails::class, $requestStubs);
        }

        $this->massProvide($requestObjects);
    }

    protected function stubRequestMethod(string $httpMethod): array
    {

        return [

            "matchesMethod" => $this->returnCallback(function ($subject) use ($httpMethod) {

                return $httpMethod == $subject;
            })
        ];
    }
}
