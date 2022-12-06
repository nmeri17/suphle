<?php
	namespace Suphle\Services;

	use Suphle\Services\Decorators\SecuresPostRequest;

	#[SecuresPostRequest]
	class ServiceCoordinator {

		public function validatorCollection ():?string {

			return null;
		}
	}
?>