<?php

namespace Suphle\Routing;

use Suphle\Routing\{Structures\PlaceholderCheck, Decorators\HandlingCoordinator};

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Contracts\{ Config\Router as RouterConfig, Routing\RouteCollection};

use Suphle\Auth\RequestScrutinizers\AuthenticateMetaFunnel;

use Suphle\Contracts\Presentation\{MirrorableRenderer, BaseRenderer};

class CollectionRouteDetector
{

    public const HAS_CHILD_NODE = "child_collection",

    HAS_RENDERER = "renderer";

    protected array $skipPatterns;

    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        protected readonly PatternIndicator $patternIndicator,
        protected readonly CollectionMethodToUrl $urlReplacer,
        protected readonly CollectionMetaQueue $collectionMetaQueue
    ) {

        //
    }

    public function compileCollectionDetails(array $skipPatterns = []): array
    {
        $this->skipPatterns = $skipPatterns;

        $routePatterns = [];

        foreach ($this->entryRouteMap() as $key => $collection) {

            $this->patternIndicator->resetIndications();

            $routePatterns[$key] = $this->getCollectionRegexDetails($collection);
        }

        return $routePatterns;
    }

    protected function getCollectionRegexDetails(string $collectionName, string $parentPrefix = ""):array
    {

        $collection = $this->container->getClass($collectionName);

        $collection->_setParentPrefix($parentPrefix);

        $collectionTree = $this->filterActivePatterns($collection);

        foreach ($collectionTree as $methodPattern => $patternDetails) {

            $collection->_invokePattern($methodPattern);

            $prefixClass = $collection->_getPrefixCollection();

            $this->patternIndicator->logPatternDetails($collection, $methodPattern); // logs all patterns into interactedPatterns. We get back every tagged funnel

            if (!empty($prefixClass)) {

                $collectionTree[$methodPattern][self::HAS_CHILD_NODE] = $this->getCollectionRegexDetails(
                    
                    $prefixClass, $collection->_prefixCurrent()
                );
            }

            else {

                $possibleRenderers = $collection->_getLastRegistered();

                if (!$collection->_expectsCrud())

                    $collectionTree[$methodPattern][self::HAS_RENDERER] = $possibleRenderers[0];

                else $collectionTree[$methodPattern][self::HAS_CHILD_NODE] = $this->extractCrudRenderers(

                    $possibleRenderers, $methodPattern
                );
            }
        }

        return $collectionTree;
    }

    protected function filterActivePatterns (RouteCollection $collection):array {

        $filteredPatterns = array_filter(
            $collection->_getPatterns(),

            fn ($pattern) => !in_array($pattern, $this->skipPatterns)
        );

        return $this->collateCollectionMethods($filteredPatterns);
    }

    protected function extractCrudRenderers (
        array $possibleRenderers, string $outerPrefix
    ):array {

        $crudDetails = $this->collateCollectionMethods(array_keys($possibleRenderers));

        $sanitizedPrefix = $this->collateCollectionMethods([$outerPrefix])[$outerPrefix]["url"];

        foreach ($possibleRenderers as $crudPath => $renderer) {

            $crudDetails[$crudPath][self::HAS_RENDERER] = $renderer;

            $crudDetails[$crudPath]["url"] = $this->collateCollectionMethods([$crudPath])[$crudPath]["url"];
        }

        return $crudDetails;
    }

    /**
     * No collection required. Just computes given patterns and returns details about them
    */
    public function collateCollectionMethods (array $patterns):array
    {

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

        if ($this->config->mirrorsCollections())

            $routeMap = array_merge($this->config->apiStack(), $routeMap);

        return $routeMap;
    }

    /**
     * @param {funnels}: Usually, array_keys(router->scrutinizerHandlers)
    */
    public function assignMetaStatus (array $funnels, array $collectionTree, array $carryPatterns = []):array {

        foreach ($collectionTree as $methodPattern => &$nodeDetail) {

            if (!is_array($nodeDetail)) continue;

            $carryPatterns[] = $methodPattern;
var_dump($methodPattern, $nodeDetail);

            if (array_key_exists(self::HAS_RENDERER, $nodeDetail)) {

                foreach ($funnels as $funnel)

                    $nodeDetail[$funnel] = $this->hasMetaFunnel($funnel, $carryPatterns);

                array_pop($carryPatterns);
            }
            elseif (array_key_exists(self::HAS_CHILD_NODE, $nodeDetail))

                $nodeDetail = $this->assignMetaStatus($funnels, $nodeDetail, $carryPatterns);
        }

        return $collectionTree;
    }

    /**
     * This can only be called after building the route tree i.e logging all patterns through the indicator
    */
    public function hasMetaFunnel (string $funnelToSearch, array $interactedPatterns):bool {

        $matchingFunnels = $this->collectionMetaQueue->findRoutedFunnels(function (CollectionMetaFunnel $funnel) use ($funnelToSearch) {

            return $funnel instanceof $funnelToSearch;
        }, $interactedPatterns);
var_dump($matchingFunnels);

        return !empty($matchingFunnels);
    }
}
