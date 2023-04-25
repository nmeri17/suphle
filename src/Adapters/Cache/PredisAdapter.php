<?php

namespace Suphle\Adapters\Cache;

use Suphle\Contracts\{IO\CacheManager, Config\CacheClient as CacheConfig};

use Predis\Client;

use Exception;

class PredisAdapter implements CacheManager
{
    final public const TAG_KEY = "_reserved_key_tags";

    protected Client $client;

    public function __construct(
        protected readonly CacheConfig $cacheConfig
    ) {

        //
    }

    public function setupClient(): void
    {

        $this->client = new Client($this->cacheConfig->getCredentials());
    }

    public function getItem(string $key, callable $storeOnAbsence = null)
    {

        $foundData = $this->client->get($key);

        if (!is_null($foundData) || is_null($storeOnAbsence)) {

            return $foundData;
        }

        $toStore = $storeOnAbsence();

        if (is_null($toStore)) {

            throw new Exception("Cache data source cannot return null");
        }

        $this->saveItem($key, $toStore);

        return $toStore;
    }

    public function saveItem(string $key, $data): void
    {

        $this->client->set($key, $data);
    }

    public function tagItem(string $key, $data): void
    {

        $dataNode = $this->client;

        $allTags = $dataNode->get(self::TAG_KEY);

        if (is_null($allTags)) {
            $allTags = [];
        }

        if (!array_key_exists($key, $allTags)) {

            $allTags[$key] = [];
        }

        $allTags[$key][] = $data;

        $dataNode->set(self::TAG_KEY, $allTags);
    }

    public function deleteItem(string $key)
    {

        $this->client->set($key, null);
    }
}
