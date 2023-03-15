<?php
	namespace Suphle\Routing\Structures;

	use Suphle\Routing\CollectionMetaFunnel;

	trait ReceivesMetaFunnel {

		protected array $metaFunnel = [];

		public function addMetaFunnel (CollectionMetaFunnel $metaFunnel):void {

			$this->metaFunnel[] = $metaFunnel;
		}
	}
?>