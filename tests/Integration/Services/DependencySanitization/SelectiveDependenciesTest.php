<?php

namespace Suphle\Tests\Integration\Services\DependencySanitization;

use Suphle\Exception\Explosives\DevError\UnacceptableDependency;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective\ForbiddenDependencyController;

class SelectiveDependenciesTest extends TestSanitization
{
    protected const FORBIDDEN = ForbiddenDependencyController::class;

    protected function setSanitizationPath(): void
    {

        $this->sanitizer->setExecutionPath($this->getClassDir(self::FORBIDDEN));
    }

    public function test_unknown_type_throws_errors()
    {

        // then
        $this->expectException(UnacceptableDependency::class);

        $this->expectExceptionMessageMatches(
            $this->escapeClassName(self::FORBIDDEN)
        );

        // given 1 @see setSanitizationPath

        $this->sanitizer->coordinatorConstructor(); // given 2

        $this->sanitizer->cleanseConsumers(); // when
    }

    public function test_filter_bad_type_runs_successfully()
    {

        $this->sanitizer->coordinatorConstructor([self::FORBIDDEN]); // given

        $this->sanitizer->cleanseConsumers(); // when

        $this->assertTrue(true);
    }
}
