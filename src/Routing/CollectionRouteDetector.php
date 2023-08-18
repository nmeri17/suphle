<?php

namespace Suphle\Routing;

use Suphle\Routing\{Structures\PlaceholderCheck, Decorators\HandlingCoordinator};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Contracts\{ Config\Router as RouterConfig, Routing\RouteCollection};

use Suphle\Contracts\Presentation\{MirrorableRenderer, BaseRenderer};

class CollectionRouteDetector
{

    protected array $skipPatterns;

    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly PatternIndicator $patternIndicator,
        protected readonly CollectionMethodToUrl $urlReplacer
    ) {

        //
    }

    public function findRenderers(array $skipPatterns = []): array
    {
        $this->skipPatterns = $skipPatterns;

        $routePatterns = [];

        foreach ($this->entryRouteMap() as $collection) {

            $this->patternIndicator->resetIndications();

            $routePatterns += $this->getCollectionRegexDetails($collection);
        }

        return $routePatterns;
    }

    protected function getCollectionRegexDetails(string $collectionName, string $parentPrefix = ""):array
    {

        $collection = $this->container->getClass($collectionName);

        $collection->_setParentPrefix($parentPrefix);

        $mutableCollectionDetails = $collectionDetails = $this->filterActivePatterns($collection);

        foreach ($collectionDetails as $methodPattern => $patternDetails) {

            $collection->_invokePattern($methodPattern);

            $prefixClass = $collection->_getPrefixCollection();

            if (!empty($prefixClass)) {

                // $this->patternIndicator->logPatternDetails($collection, $methodName); // for this to work, we need new instances for each collection. Or just update its entry

                $mutableCollectionDetails[$methodPattern]["child_collection"] = $this->getCollectionRegexDetails(
                    
                    $prefixClass, $collection->_prefixCurrent()
                );
            }

            else {

                $possibleRenderers = $collection->_getLastRegistered(); // if it's a crud, indicate on this outer logger by adding all those urls. otherwise, descend and add those children under a key named as such

                if (!$collection->_expectsCrud())

                    $mutableCollectionDetails[$methodPattern]["renderer"] = $possibleRenderers[0];

                else $mutableCollectionDetails = $this->extractCrudRenderers(

                    $possibleRenderers, $mutableCollectionDetails,

                    $methodPattern
                );
            }
        }

        return $mutableCollectionDetails;
    }

    protected function filterActivePatterns (RouteCollection $collection):array {

        $filteredPatterns = array_filter(
            $collection->_getPatterns(),

            fn ($pattern) => !in_array($pattern, $this->skipPatterns)
        );

        return $this->collateCollectionMethods($filteredPatterns);
    }

    protected function extractCrudRenderers (array $possibleRenderers, array $collectionDetails, string $outerPrefix):array {

        $crudDetails = $this->collateCollectionMethods(array_keys($possibleRenderers));

        foreach ($possibleRenderers as $crudPath => $renderer) {

            $crudNodeDetail = $crudDetails[$crudPath];

            $crudNodeDetail["renderer"] = $renderer;

            $collectionDetails[$outerPrefix."_".$crudPath] = $crudNodeDetail;
        }

        return $collectionDetails;
    }

    public function collateCollectionMethods (array $patterns):array
    {

        /*
        * leaving this in, to see how the parser reacts to index patterns
        $indexMethodIndex = array_search(RouteCollection::INDEX_METHOD, $patterns);

        if ($indexMethodIndex !== false) {

            return new PlaceholderCheck("", RouteCollection::INDEX_METHOD);

            unset($patterns[$indexMethodIndex]); // since we're sure it's not the one, no need to confuse the other guys, who will always "partially" match an empty string
        }*/

        return array_map(
            function ($methodDetails) {

                $methodDetails["url"] = str_replace(

                    "/?", "/", (string) $methodDetails["url"]
                );

                return $methodDetails;
            },
            $this->patternPlaceholderDetails($patterns)
        );
    }

    /**
     * @param {patterns}: The list of patterns found on the currently evaluated collection
     * 
     * @return returns a new array mapping each pattern to its details irrespective of whether it has placeholder details or not. e.g ['OUTER__index' => ["url" => "OUTER/_", "placeholders" => []]]
    */
    public function patternPlaceholderDetails(array $patterns): array
    {

        $values = array_map(function ($pattern) {

            $result = $this->urlReplacer->replacePlaceholders(
                $pattern,

                CollectionMethodToUrl::REPLACEMENT_TYPE_PLACEHOLDER
            );

            return [
                "url" => $result->regexifiedUrl(),

                "placeholders" => $result->getPlaceholders()
            ];
        }, $patterns);

        return array_combine($patterns, $values);
    }

    /**
     * @return class-string<RouteCollection>[]
    */
    public function entryRouteMap(): array
    {

        $entryRoute = $this->config->browserEntryRoute();

        $hasEntry = !is_null($entryRoute);

        $routeMap = [];

        if ($hasEntry) $routeMap[] = $entryRoute;

        if ($this->config->mirrorsCollections()) // entry goes to the bottom

            $routeMap = [...$this->config->apiStack(), ...$routeMap];

        return $routeMap;
    }
}
