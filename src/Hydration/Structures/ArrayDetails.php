<?php

namespace Suphle\Hydration\Structures;

class ArrayDetails
{
    public function removeAtIndex(array $removeFrom, string $toRemove): array
    {

        $index = array_search($toRemove, $removeFrom);

        if (isset($removeFrom[$index])) {

            unset($removeFrom[$index]);

            $removeFrom = array_values($removeFrom);
        }

        return $removeFrom;
    }
}
