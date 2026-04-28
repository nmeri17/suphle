<?php
namespace Suphle\Routing\Analysis;

use ReflectionMethod;

trait DocBlockParser
{
    /**
     * Returns all lines not starting with @ as a string
     */
    public function extractMethodDescription(): string
    {
        $doc = $this->actionMethod->getDocComment();

        $cleaned = [];

        if ($doc) foreach (explode("\n", $doc) as $line) {

            $line = trim($line, " \t\n\r\0\x0B*/");

            if (str_starts_with($line, "@")) continue; // @param and @return are captured more accurately by the psalm parser so there's no need maintaining a manual, hardcoded version
            if (!empty($line)) $cleaned[] = $line;
        }
        
        return empty($cleaned)?
            ucfirst($this->actionMethod->getName()): // Fallback to method name 
            implode(" ", $cleaned);
    }
}