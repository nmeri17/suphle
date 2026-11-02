<?php

namespace Suphle\Tests\Integration\Services\CoodinatorManager;

use Suphle\Security\CSRF\CsrfGenerator;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Exception\Explosives\ValidationFailure;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Coordinators\ValidatorCoordinator};

class ValidatorCoordinatorNotRunTest extends ModuleLevelTest
{
    protected bool $debugCaughtExceptions = true;

    protected string $coordinatorName = ValidatorCoordinator::class;

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "getCoordinatorClassesToScan" => [ValidatorCoordinator::class]
                ]);
            })
        ];
    }

    public function test_failure_prevents_action_handling()
    {

        $this->expectException(ValidationFailure::class);

        // given @see validation rules
        $this->get($this->expandUrl("handleGet"))->assertOk();

        $this->massProvide([

            ValidatorCoordinator::class => $this->positiveDouble(
                ValidatorCoordinator::class,
                [],
                [

                "postWithValidator" => [0, []] // then
                ]
            )
        ]);

        $this->post($this->expandUrl("postWithValidator"), [

            CsrfGenerator::TOKEN_FIELD => $this->getContainer()

            ->getClass(CsrfGenerator::class)->newToken()
        ]);
    }
}
