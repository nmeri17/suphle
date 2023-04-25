<?php

namespace Suphle\Testing\TestTypes;

use Suphle\Contracts\{Presentation\BaseRenderer, Modules\DescriptorInterface};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Modules\ModuleExceptionBridge;

use Suphle\Exception\DetectedExceptionManager;

use Suphle\Testing\Condiments\{ BaseModuleInteractor, ModuleReplicator};

use Suphle\Testing\Proxies\{ModuleHttpTest, Extensions\FrontDoor};

use PHPUnit\Framework\{ ExpectationFailedException, MockObject\Stub\Stub};

use Throwable;
use Exception;

abstract class InvestigateSystemCrash extends TestVirginContainer
{ // extending from this for sub classes to have access to [dataProvider]

    use BaseModuleInteractor;
    use ModuleReplicator;
    use ModuleHttpTest;

    protected const BRIDGE_NAME = ModuleExceptionBridge::class;

    protected const BROADCASTER_NAME = DetectedExceptionManager::class;

    protected ObjectDetails $objectMeta;

    protected bool $softenDisgraceful = false; // prevents us from stubbing [BRIDGE_NAME]; we'll use the real one i.e. so that disgracefulShutdown can run

    protected function setUp(): void
    {

        $this->entrance = new FrontDoor($this->modules = [$this->getModule()]);

        $this->provideTestEquivalents();

        $this->bootMockEntrance($this->entrance);

        $this->objectMeta = $this->getContainer()->getClass(ObjectDetails::class);
    }

    protected function getContainer(): Container
    {

        return $this->firstModuleContainer();
    }

    abstract protected function getModule(): DescriptorInterface;

    protected function assertWontBroadcast(callable $flammable)
    {

        return $this->executeHandlerDoubles(0, $flammable);
    }

    /**
     * For this to run during a request, all exception handling has to fail
    */
    protected function assertWillBroadcast(callable $flammable)
    {

        return $this->executeHandlerDoubles(1, $flammable);
    }

    /**
     *
     * @return $flammable result
    */
    private function executeHandlerDoubles(int $numTimes, callable $flammable)
    {

        $this->bindBroadcastAlerter($numTimes, [

            $this->anything(), $this->anything() // consider removing
        ]);

        if ($this->softenDisgraceful) {

            $this->stubExceptionBridge();
        }

        return $flammable();
    }

    /**
     * We only want to bind this when we want to verify payload going to the broadcaster. It's not for exceptions since exceptions don't come as objects during shutdown
    */
    protected function bindBroadcastAlerter(int $numTimes, array $argumentList): void
    {

        $this->getContainer()->whenTypeAny()->needsAny([

            self::BROADCASTER_NAME => $this->mockBroadcastAlerter($numTimes, $argumentList)
        ]);
    }

    /**
     * @param {argumentList}: Mock verifications for the alerter method
    */
    protected function mockBroadcastAlerter(int $numTimes, array $argumentList): DetectedExceptionManager
    {

        return $this->replaceConstructorArguments(
            self::BROADCASTER_NAME,
            $this->broadcasterArguments(),
            [],
            [

            DetectedExceptionManager::ALERTER_METHOD => [$numTimes, $argumentList]
            ]
        );
    }

    /**
     * @return Constructor arguments to use in creating double for DetectedExceptionManager
    */
    protected function broadcasterArguments(): array
    {

        return [];
    }

    /**
     * What this does is prevent [disgracefulShutdown] from running when [gracefulShutdown] fails. In order for it to be useful, you'd have to violate DetectedExceptionManager::ALERTER_METHOD by not calling it (mocked before we got here), or throwing another error from [gracefulShutdown]
    */
    protected function stubExceptionBridge(array $stubMethods = [], array $mockMethods = []): void
    {

        $parameters = $this->getContainer()->getMethodParameters(
            Container::CLASS_CONSTRUCTOR,
            self::BRIDGE_NAME
        );

        $defaultStubs = [

            "disgracefulShutdown" => $this->returnCallback(function ($errorDetails, $latestException) {

                throw $latestException;
            }),

            "writeStatusCode" => null
        ];

        $this->massProvide([

            self::BRIDGE_NAME => $this->replaceConstructorArguments(
                self::BRIDGE_NAME,
                $parameters,
                array_merge($defaultStubs, $stubMethods),
                $mockMethods
            )
        ]);
    }

    /**
     * The bridge stubbed here is the one used by entrance, since it only looks for that object when triggered by handling a request
     *
     * @param {exception}: Should either be expected exception or its super class
     *
     * @param {flammable}: If exception is indeed thrown, and this callback contains an HTTP request, renderer returned will be a dummy one since we'll be unable to evaluate a real one
    */
    protected function assertWillCatchException(string $exceptionName, callable $flammable, string $exceptionMessage = null): void
    {

        $this->stubExceptionBridge([

            "handlingRenderer" => $this->positiveDouble(BaseRenderer::class, [

                "getRawResponse" => [], "getStatusCode" => 500
            ])
        ], [

            "hydrateHandler" => [1, [

                $this->callback(function ($subject) use ($exceptionName, $exceptionMessage) {

                    $receivedException = $subject::class;

                    $matchesException = $receivedException === $exceptionName ||

                    $this->objectMeta->implementsInterface($exceptionName, $receivedException); // we would've used assertInstanceOf here, but if that fails, it swallows context of the original error and throws the assertInstanceOf one, instead

                    $matchesMessage = !$exceptionMessage ? true :

                    preg_match("/$exceptionMessage/i", $subject);

                    return $matchesException && $matchesMessage;
                })
            ]]
        ]);

        $flammable();
    }

    /**
     * Compares renderer handlers
     * This can only run if exception was caught i.e. not during app shutdown
    */
    protected function assertExceptionUsesRenderer(BaseRenderer $renderer, callable $flammable): void
    {

        if ($this->softenDisgraceful) {

            $this->stubExceptionBridge();
        }

        try {

            $flammable();
        } catch (Throwable $exception) {

            $this->entrance->findExceptionRenderer($exception);

            $resolvedRenderer = $this->entrance->underlyingRenderer();

            $this->assertTrue(
                $renderer->matchesHandler($resolvedRenderer->getHandler()),
                "Failed asserting that exception '". $exception::class . "' was handled with given renderer"
            );
        }
    }

    protected function debugCaughtException(): void
    {

        $this->stubExceptionBridge([

            "hydrateHandler" => $this->returnCallback(function ($subject): never {

                throw $subject;
            })
        ]);
    }
}
