<?php
namespace Suphle\Tests\Integration\Services\CoodinatorManager;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Middleware\Handlers\FinalHandlerWrapper;

use Suphle\Exception\Explosives\{DevError\NoCompatibleValidator, ValidationFailure};

use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\ValidatorCoordinator};

class ValidatorRawErrorsTest extends ModuleLevelTest
{
    protected bool $debugCaughtExceptions = true;

    protected string $coordinatorName = ValidatorCoordinator::class;

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [$this->coordinatorName]
                ]);
            })
        ];
    }

    public function test_get_thows_no_errors_on_rule_absence()
    {

        $this->get($this->expandUrl("handleGet")) // when

        ->assertOk(); // then
    }

    /**
     * @dataProvider notGetUrls
    */
    public function test_other_methods_requires_validation(string $url)
    {

        $this->expectException(ValidationFailure::class); // then

        // given @see coordinator validator rules

        $this->post($url); // when
    }

    public function notGetUrls(): array
    {

        return [

            [$this->expandUrl("postWithValidator")],

            [$this->expandUrl("postWithValidatorRedirect")]
        ];
    }

    public function test_other_method_without_validation_fails()
    {

        $this->expectException(NoCompatibleValidator::class); // then

        // given @see coordinator validator rules

        $this->post($this->expandUrl("postNoValidator")); // when
    }


    public function test_failed_validation_throws_error()
    {

        $this->expectException(ValidationFailure::class); // then

        $this->post($this->expandUrl("postWithValidator")); // when
    }

    public function test_failure_prevents_middleware_running()
    {

        $this->middlewareWillRun(1);

        // given @see validation rules
        $this->get($this->expandUrl("handleGet"));

        $this->expectException(ValidationFailure::class);

        $this->middlewareWillRun(0); // after it's been wiped

        $this->post($this->expandUrl("postWithValidator")); // when
    }

    protected function middlewareWillRun(int $numTimes): void
    {

        $this->massProvide([

            FinalHandlerWrapper::class => $this->positiveDouble(FinalHandlerWrapper::class, [ ], [

                "process" => [$numTimes, []] // then
            ])
        ]);
    }
}
