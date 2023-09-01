<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector;

use Suphle\Routing\CollectionRouteDetector;

trait RouteDetectorAsserter {

    protected function assertFoundGivenPatterns (array $collectionDetails, array $expectedUrls):void {

        $matchedAll = true;

    	foreach ($expectedUrls as $detailEntry) {

            $matchedAll = !empty(array_filter($collectionDetails,

                fn ($details) => $detailEntry[0] == strtolower($details["url"]) // this assertion doesn't really care about placeholder positions--just that the pattern matches
            ));

            if (!$matchedAll) {

                var_dump($detailEntry, $collectionDetails);

                break;
            }
        }

        $this->assertTrue($matchedAll);
    }

    /**
     * Produced urls are suffixed with trailing slashes. So, to be recognised as a match, given paths must equally have that
    */
    protected function assertNotFoundGivenPatterns (array $collectionDetails, array $expectedUrls):void {

        $missedAll = true;

        foreach ($expectedUrls as $detailEntry) {

            $missedAll = empty(array_filter($collectionDetails,

                fn ($details) => $detailEntry[0] == strtolower($details["url"])
            ));

            if (!$missedAll) {

                var_dump($detailEntry, $collectionDetails);

                break;
            }
        }

        $this->assertTrue($missedAll);
    }

    protected function getDetector ():CollectionRouteDetector {

        return $this->getContainer()

        ->getClass(CollectionRouteDetector::class);
    }

    protected function assertMatchesChildPatterns (array $routeTree, string $toSearch):void {

        $foundMatch = false;

        $toSearch = rtrim($toSearch, "/");

        foreach ($routeTree as $patternDetails) {

            $detailUrl = rtrim(strtolower($patternDetails["url"]), "/");

            $foundMatch = str_contains($toSearch, $detailUrl);

            if (!$foundMatch) continue;

            $remainder = explode($detailUrl, $toSearch)[1];

            if (!empty($remainder)) {

                $this->assertMatchesChildPatterns(
                    $patternDetails["child_collection"],

                    $remainder
                );

                break;
            }
        }

        $this->assertTrue($foundMatch);
    }
}