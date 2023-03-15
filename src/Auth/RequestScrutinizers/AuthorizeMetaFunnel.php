<?php
	namespace Suphle\Auth\RequestScrutinizers;

	use Suphle\Routing\CollectionMetaFunnel;

	class AuthorizeMetaFunnel extends CollectionMetaFunnel {

		public function __construct (

			protected readonly array $activePatterns,

			public readonly string $ruleClass
		) {

			//
		}
	}
?>