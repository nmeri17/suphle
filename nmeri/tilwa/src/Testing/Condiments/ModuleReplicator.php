<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Testing\Proxies\WriteOnlyContainer;

	trait ModuleReplicator {

		/**
		 * A blank container is given to the new module, with the assumption that we possibly wanna overwrite even the default objects (aside from only injecting absent configs)
		*/
		protected function replicateModule(string $descriptor, callable $customizer):ModuleDescriptor {

			$writer = new WriteOnlyContainer; // using unique instances rather than a fixed one so test can make multiple calls to clone modules

			$customizer($writer);

			return new $descriptor($writer->getContainer());
		}
	}
?>