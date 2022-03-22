<?php
	namespace Tilwa\Bridge\Laravel\Config;

	use Tilwa\Contracts\Config\ConfigMarker;

	class BaseConfigLink implements ConfigMarker {

		protected $nativeValues;

		public function __construct (array $nativeValues) {

			$this->nativeValues = $nativeValues;
		}

		public function getNativeValues ():array {

			return $this->nativeValues;
		}
	}
?>