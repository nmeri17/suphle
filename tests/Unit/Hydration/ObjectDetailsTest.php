<?php

namespace Suphle\Tests\Unit\Hydration;

use Suphle\Hydration\Structures\ObjectDetails;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\CConcrete;

use ReflectionClass;

class ObjectDetailsTest extends IsolatedComponentTest
{
    public function test_get_full_class_namespace()
    {

        $sut = $this->container->getClass(ObjectDetails::class);

        $classPath = (new ReflectionClass(IsolatedComponentTest::class))
        ->getFileName(); // given

        $derivedNamespace = $sut->classNameFromFile($classPath); // when

        $this->assertSame( // then

            $derivedNamespace,
            "Suphle\Testing\TestTypes\IsolatedComponentTest"
        );
    }

    public function test_get_namespace_of_root_classes() // copy it there
    {$sut = $this->container->getClass(ObjectDetails::class);

        $classPath = (new ReflectionClass(CConcrete::class))

        ->getFileName();

        $directoryPath = dirname($classPath). DIRECTORY_SEPARATOR . "InRootNamespace.php"; // given

        $derivedNamespace = $sut->classNameFromFile($directoryPath); // when

        $this->assertSame( // then

            $derivedNamespace,
            "Suphle\InRootNamespace"
        );
    }
}
