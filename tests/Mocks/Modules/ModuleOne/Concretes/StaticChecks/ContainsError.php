<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\StaticChecks;

class ContainsError
{
    public function echoTypo(): void
    {

        $animal = "cat";

        echo $animalx;
    }
}
