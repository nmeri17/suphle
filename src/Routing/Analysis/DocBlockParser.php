<?php

namespace Suphle\Routing\Analysis;

use ReflectionMethod;

trait DocBlockParser
{
    protected function extractMethodSummary(ReflectionMethod $method): string
    {
        $doc = $method->getDocComment();
        if (!$doc) return ucfirst($method->getName());
        foreach (explode("\n", $doc) as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (!empty($line) && !str_starts_with($line, "@")) return $line;
        }
        return ucfirst($method->getName());
    }

    protected function extractMethodDescription(ReflectionMethod $method): string
    {
        $doc = $method->getDocComment();
        if (!$doc) return "";
        $description = [];
        foreach (explode("\n", $doc) as $line) {
            $line = trim($line, " \t\n\r\0\x0B*/");
            if (str_starts_with($line, "@")) break;
            if (!empty($line)) $description[] = $line;
        }
        return implode(" ", $description);
    }
}