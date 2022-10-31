<?php
	namespace Suphle\Config;

	use Suphle\Contracts\{Config\CacheClient, IO\EnvAccessor};

	class DefaultCacheConfig implements CacheClient {

		public function __construct(private readonly EnvAccessor $envAccessor)
  {
  }

		public function getCredentials ():array {

			return [

				"scheme" => "tcp",

				"host" => $this->envAccessor->getField("REDIS_HOST"),

				"port" => $this->envAccessor->getField("REDIS_PORT")
			];
		}
	}
?>