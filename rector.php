<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->paths([
        __DIR__ . '/src',

        __DIR__ . '/tests',
    ]);

    $rectorConfig->parallel(4 * 60);

    $rectorConfig->skip([
        __DIR__ . '/src/Hydration/Templates/CircularBreaker.php'
    ]);

    // register a single rule
    $rectorConfig->rule(SplitGroupedConstantsAndPropertiesRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82
    ]);
};
