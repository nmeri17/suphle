<?php

namespace Suphle\Tests\Integration\Routing\Nested;

use Suphle\Hydration\Container;

use Suphle\Contracts\Config\Router;

use Suphle\Routing\{PatternIndicator, PreMiddlewareRegistry};

use Suphle\Request\RequestDetails;

use Suphle\Middleware\MiddlewareRegistry;

use Suphle\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\Collectors\BlankCollectionMetaFunnel};

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\{ActualEntry, Secured\MisleadingEntry};

class MultiCollectionsTest extends ModuleLevelTest
{
    private string $threeTierUrl = "/api/v2/first/middle/third";

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "apiStack" => [

                        "v2" => MisleadingEntry::class,

                        "v1" => ActualEntry::class
                    ]
                ]);
            })
        ];
    }

    public function test_needs_recovery_from_misleading_trail()
    {

        $this->removeIndicatorResetter(); // given

        $this->get($this->threeTierUrl) // when

        // then
        ->assertUnauthorized(); // misleading authenticates while eventual doesn't. If this assertion fails, recovery functionality is redundant

        $this->assertUsedCollectorNames([BlankCollectionMetaFunnel::class]); // Misleading collection tags BlankCollectionMetaFunnel, but the eventual collection group doesn't
    }

    private function removeIndicatorResetter()
    {

        $constructorStubs = [

            MiddlewareRegistry::class => $this->positiveDouble(MiddlewareRegistry::class),

            PreMiddlewareRegistry::class => $this->positiveDouble(PreMiddlewareRegistry::class)
        ];

        $this->massProvide(array_merge($constructorStubs, [ // also bind their stubs for any other collaborator to use those instances the indicator is writing to

            PatternIndicator::class => $this->getPatternIndicator($constructorStubs)
        ]));
    }

    protected function getPatternIndicator(array $constructorStubs): PatternIndicator
    {

        return $this->replaceConstructorArguments(
            PatternIndicator::class,
            array_merge($constructorStubs, [

                RequestDetails::class => $this->positiveDouble(RequestDetails::class, [ // it's impossible to know this object before a request. Besides, doing so will cause sut to get wiped while executing the test url

                    "isApiRoute" => true
                ])
            ]),
            [

            "resetIndications" => null
            ]
        );
    }

    public function test_can_detach_quantities_after_each_entry_collection()
    {

        $this->get($this->threeTierUrl) // when

        // then
        ->assertOk();

        $this->assertDidntUseCollectorNames([BlankCollectionMetaFunnel::class]);
    }
}
