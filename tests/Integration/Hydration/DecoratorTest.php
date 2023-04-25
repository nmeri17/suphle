<?php

namespace Suphle\Tests\Integration\Hydration;

use Suphle\Hydration\{DecoratorHydrator, Structures\CallbackDetails};

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, ValidationRules};

use Suphle\Testing\{TestTypes\IsolatedComponentTest, Utilities\ArrayAssertions};

use Suphle\Tests\Integration\Generic\CommonBinds;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\{ThrowsException, Services\SystemModelEditMock1};

use Suphle\Tests\Mocks\Modules\ModuleOne\Authentication\CustomBrowserRepo;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;

use Throwable;
use ReflectionAttribute;

class DecoratorTest extends IsolatedComponentTest
{
    use CommonBinds;
    use ArrayAssertions;

    private DecoratorHydrator $hydrator;

    protected function setUp(): void
    {

        parent::setUp();

        $this->hydrator = $this->container->getClass(DecoratorHydrator::class);
    }

    public function test_getRelevantDecors_gets_correct_list()
    {

        $decoratorToHandler = [

            InterceptsCalls::class => null,

            VariableDependencies::class => null
        ];

        $result = $this->hydrator->getRelevantDecors(
            $decoratorToHandler,
            SystemModelEditMock1::class
        );

        foreach ($decoratorToHandler as $decoratorName => $handler) {

            $this->assertArrayHasKey($decoratorName, $result);

            $this->assertInstanceOf(
                ReflectionAttribute::class,
                $result[$decoratorName][0]
            );
        }
    }

    public function test_catches_error() // proof of concept
    {$awesomeClass = new ThrowsException();

        $sut = (new AccessInterceptor())->createProxy($awesomeClass, [

            "awesomeMethod" => function ($proxy, $concrete, $method, $parameters, &$earlyReturn) { // we control this, only releasing concrete method and paramters

                try {
                    $result = $concrete->$method();
                } catch (Throwable) {

                    $result = 48;
                }

                $earlyReturn = true; // for all methods

                return $result;
            }
        ], [

            "awesomeMethod" => function ($proxy, $concrete, $method, $parameters, $result, &$earlyReturn) {

                var_dump(47, $concrete, $result); // early return means this won't run

                $earlyReturn = true;

                return 63;
            }
            ]);

        $this->assertSame(48, $sut->awesomeMethod());
    }

    public function test_extended_class_attribute_is_most_recent()
    {

        $allRules = $this->container->getClass(CallbackDetails::class)
        ->getMethodAttributes(
            CustomBrowserRepo::class,
            "successLogin", // given // attributes on this method

            ValidationRules::class
        );

        $mostRecent = end($allRules)->newInstance()->rules; // when

        $this->assertAssocArraySubset([

            "password" => "required|numeric|min:9"
        ], $mostRecent); // then
    }
}
