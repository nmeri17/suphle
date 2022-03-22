<?php
	namespace Tilwa\Tests\Unit\Hydration\Extensions;

	use Tilwa\Hydration\Container;

	class CheckProvisionedClasses extends Container {

		public function matchesNeedsProvision (string $caller, array $provisions):bool {

			if (!array_key_exists($caller, $this->provisionedClasses))

				return false;

			$context = $this->provisionedClasses[$caller];

			foreach ($provisions as $entityName => $concrete)

				if ( !$context->hasConcrete($entityName) || $context->getConcrete($entityName) != $concrete)

					return false;

			return true;
		}

		public function matchesArgumentProvision (string $caller, array $provisions):bool {

			if (!array_key_exists($caller, $this->provisionedClasses))

				return false;

			$context = $this->provisionedClasses[$caller];

			foreach ($provisions as $entityName => $concrete)

				if ( !$context->hasArgument($entityName) || $context->getArgument($entityName) != $concrete)

					return false;

			return true;
		}
	}
?>