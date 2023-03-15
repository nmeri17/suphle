<?php
	namespace Suphle\Routing\Structures;

	use Suphle\Routing\CollectionMetaFunnel;

	use Closure;

	class CollectionMetaExclusion {

		public function __construct (

			protected readonly string $funnelName,

			protected readonly ?Closure $matcher = null
		) {

			//
		}

		public function shouldExclude (CollectionMetaFunnel $collector):bool {

			$matchesType = $collector instanceof $this->funnelName;

			$matcherResult = true;

			if (!is_null($this->matcher))

				$matcherResult = !call_user_func_array($this->matcher, [$collector]); // returning the inverse i.e. remove collector if callback returns true

			return $matchesType && $matcherResult;
		}
	}
?>