<?php
	namespace Suphle\Routing;

	class MethodSorter {

		/**
		 * Move longer patterns up so shorter ones don't misleadingly swallow partly matching segments
		*/
		private function descendingCallback ($a, $b):int {

			$aLength = strlen($a);

			$bLength = strlen($b);

			if ($aLength == $bLength) return 0;

			return ($bLength > $aLength) ? 1: -1; // push greater right upwards ie descending
		}

		public function descendingValues (array $patterns):array {

			usort($patterns, [$this, "descendingCallback"]);

			return $patterns;
		}

		public function descendingKeys (array $patterns):array {

			uksort($patterns, [$this, "descendingCallback"]);

			return $patterns;
		}
	}
?>