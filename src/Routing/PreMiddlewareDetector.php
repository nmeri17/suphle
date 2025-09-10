<?php

namespace Suphle\Routing;

use Suphle\Routing\Attributes\PreMiddleware;
use ReflectionClass;
use ReflectionMethod;

class PreMiddlewareDetector
{
    public function detectPreMiddleware(string $coordinatorClass, string $methodName = null): array
    {
        $reflection = new ReflectionClass($coordinatorClass);
        $preMiddleware = [];

        // Check class-level PreMiddleware
        $classAttributes = $reflection->getAttributes(PreMiddleware::class);
        foreach ($classAttributes as $attribute) {
            $instance = $attribute->newInstance();
            $preMiddleware[] = $instance->funnelClass;
        }

        // Check method-level PreMiddleware (overrides class-level)
        if ($methodName) {
            $method = $reflection->getMethod($methodName);
            $methodAttributes = $method->getAttributes(PreMiddleware::class);
            foreach ($methodAttributes as $attribute) {
                $instance = $attribute->newInstance();
                $preMiddleware[] = $instance->funnelClass;
            }
        }

        return $preMiddleware;
    }
} 