<?php

namespace Suphle\Testing\Utilities;

trait ArrayAssertions
{
    protected function assertAssocArraySubset(array $toVerify, array $payload): void
    {

        foreach ($toVerify as $key => $value) {

            $this->assertArrayHasKey($key, $payload);

            $this->assertSame($value, $payload[$key]);
        }
    }
}
