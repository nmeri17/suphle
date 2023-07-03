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

    protected function assertArrayHasPath (array $payload, string $path) {

        if (str_contains($path, ".")) {

            $segments = explode(".", $path);

            $current = array_shift($segments);

            return $this->assertArrayHasPath(

                $payload[$current], implode(".", $segments)
            );
        }
        
        return $this->assertArrayHasKey($path, $payload);
    }
}
