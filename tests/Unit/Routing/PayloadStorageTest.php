<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Request\PayloadStorage;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

class PayloadStorageTest extends IsolatedComponentTest
{
    use CommonBinds;

    protected bool $usesRealDecorator = false;

    private array $samplePayload = ["foo" => 1, "bar" => 2, "fooBar" => 3];

    public function test_onlyMethod_correctly_filters()
    {

        $sut = $this->positiveDouble(PayloadStorage::class, [

            "fullPayload" => $this->samplePayload
        ]); // given

        $result = $sut->only(["foo"]); // when

        $this->assertSame(["foo" => 1], $result); // then
    }

    public function test_exceptMethod_correctly_filters()
    {

        $sut = $this->positiveDouble(PayloadStorage::class, [

            "fullPayload" => $this->samplePayload
        ]); // given

        $result = $sut->except(["foo"]); // when

        $this->assertSame(["bar" => 2, "fooBar" => 3], $result); // then
    }
}
