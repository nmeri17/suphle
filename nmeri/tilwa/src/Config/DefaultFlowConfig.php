<?php
	namespace Tilwa\Config;

	use Tilwa\Contracts\Config\Flows;

	use Illuminate\Support\Collection as LaravelCollection;

	class DefaultFlowConfig implements Flows {

		public function contentTypeIdentifier ():array {

			return [
				
				LaravelCollection::class => "getQueueableClass"
			];
		}
	}
?>