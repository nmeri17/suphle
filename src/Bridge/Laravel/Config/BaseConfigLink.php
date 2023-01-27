<?php
	namespace Suphle\Bridge\Laravel\Config;

	use Suphle\Contracts\Config\ConfigMarker;

	class BaseConfigLink implements ConfigMarker {

		public function __construct(protected array $nativeValues) {

			//
		}

		public function getNativeValues ():array {

			return $this->nativeValues;
		}
	}
?>