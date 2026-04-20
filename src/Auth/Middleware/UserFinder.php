<?php
namespace Suphle\Auth\Middleware;

use Suphle\Auth\Storage\SessionStorage;

use Suphle\Contracts\Auth\AuthStorage;

trait UserFinder {

	protected AuthStorage $storage;

	protected function tryGetUserId ():?string {

        if (empty($this->args)) $storageName = SessionStorage::class;

        else $storageName = current($this->args);

        $this->storage = $this->container->getClass($storageName); // it is expected that no one would require auth until this point. nobody should be using any bound auth instance anyway since that's supposed to be dictated by target route, which is handled here

        return $this->storage->getId();
    }
}