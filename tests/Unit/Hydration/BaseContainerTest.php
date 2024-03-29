<?php

namespace Suphle\Tests\Unit\Hydration;

use Suphle\Hydration\{Container, Structures\ContainerTelescope};

use Suphle\Contracts\Auth\UserContract;

use Suphle\Testing\TestTypes\TestVirginContainer;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{ ARequiresBCounter, BCounter};

use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\CInterface;

use ReflectionMethod;
use Exception;
use stdClass;

class BaseContainerTest extends TestVirginContainer
{
    private $container;
    private $aRequires = ARequiresBCounter::class;

    protected function setUp(): void
    {

        $this->container = new Container();

        $this->container->setEssentials();
    }

    public function test_decorateProvidedConcrete_doesnt_overflow_memory()
    {

        $sut = $this->positiveDouble(Container::class, [], [

            "getProvidedConcrete" => [1, [$this->anything()]]
        ]); // then

        $sut->setEssentials();

        $sut->decorateProvidedConcrete($this->aRequires); // when
    }

    public function test_can_provide_arguments()
    {

        $containerTelescope = new ContainerTelescope();

        $container = $this->container;

        $container->setTelescope($containerTelescope);

        $bCounter = new BCounter();

        $container->whenType($this->aRequires)->needsAny([ // given

            BCounter::class => $bCounter
        ])
        ->getClass($this->aRequires); // when

        $this->assertTrue($containerTelescope->readArgumentFor(
            $this->aRequires,
            [

                "b1" => $bCounter
            ]
        ));
    }

    public function test_getClass_tries_returning_provided()
    {

        $stub = new stdClass();

        $sut = $this->positiveDouble(Container::class, [

            "decorateProvidedConcrete" => $stub // given
        ]);

        $this->assertSame( // then
            $sut->getClass($this->aRequires), // when

            $stub
        );
    }

    public function test_unprovided_get_to_decorateProvidedConcrete_returns_null()
    {

        $this->assertNull($this->container->decorateProvidedConcrete($this->aRequires));
    }

    public function test_getClass_tries_to_instantiate_concrete()
    {

        // given
        $sut = $this->positiveDouble(Container::class, [], [

            "instantiateConcrete" => [ // then
                $this->atLeastOnce(), [
                    $this->equalTo($this->aRequires)
                ]
            ]
        ]);

        $this->bootContainer($sut);

        $sut->getClass($this->aRequires); // when
    }

    public function test_can_directly_instantiate_concrete_without_interface()
    {

        // given
        $sut = $this->withArgumentsForARequires([

            "getDecorator" => $this->stubDecorator()
        ]);

        $this->bootContainer($sut);

        $this->assertInstanceOf( // then
            $this->aRequires,
            $sut->instantiateConcrete($this->aRequires) // when
        );
    }

    private function withArgumentsForARequires(array $otherOverrides = [])
    {

        return $this->positiveDouble(Container::class, array_merge([

            "getMethodParameters" => array_merge($this->manuallyStubify([BCounter::class]), [""])
        ], $otherOverrides));
    }

    public function test_can_hydrate_method_parameters_without_interface()
    {

        $sut = $this->positiveDouble(Container::class, [

            "lastHydratedFor" => $this->aRequires,

            "getDecorator" => $this->stubDecorator()
        ]);

        $this->bootContainer($sut);

        // given
        $reflectedCallable = new ReflectionMethod($this->aRequires, Container::CLASS_CONSTRUCTOR);

        $provisionContext = $sut->getRecursionContext();

        $parameters = $sut->populateDependencies($reflectedCallable, $provisionContext); // when

        // then
        $this->assertTrue(is_string($parameters["primitive"]));

        $this->assertInstanceOf(BCounter::class, $parameters["b1"]);
    }

    public function test_internal_get_parameters_calls_populateDependencies()
    {

        // given
        $sut = $this->positiveDouble(Container::class, [

            "getDecorator" => $this->stubDecorator()
        ], [

            "populateDependencies" => [1, [

                $this->anything(), $this->anything() // not null, since there's always a context when a class method is being populated (fallback to universal)
            ]] // then
        ]);

        $this->bootContainer($sut);

        $parameters = $sut->internalMethodGetParameters($this->aRequires, function ($className) use ($sut) {

            return $sut->getMethodParameters(Container::CLASS_CONSTRUCTOR, $className);
        });
    }

    private function manuallyStubify(array $types): array
    {

        return array_map(function ($type) {

            return $this->positiveDouble($type);
        }, $types);
    }

    public function test_request_for_interface_skip_bridge_calls_provideInterface()
    {

        $interfaceName = UserContract::class;

        $sut = $this->positiveDouble(Container::class, [

            "getDecorator" => $this->stubDecorator()
        ], [

            "provideInterface" => [1, [$interfaceName]], // then
        ]);

        $this->bootContainer($sut);

        $sut->getClass($interfaceName);
    }

    public function test_hydrating_interface_without_bind_will_terminate()
    {

        $this->expectException(Exception::class); // then

        $sut = $this->positiveDouble(Container::class, [

            "getDecorator" => $this->stubDecorator()
        ]);

        $this->bootContainer($sut);

        $this->withDefaultInterfaceCollection($sut);

        $sut->getClass(CInterface::class); // when
    }
}
