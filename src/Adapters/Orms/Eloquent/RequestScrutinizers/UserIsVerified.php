<?php
	namespace Suphle\Adapters\Orms\Eloquent\RequestScrutinizers;

	use Suphle\Routing\Structures\BaseScrutinizerHandler;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Exception\Explosives\UnverifiedAccount;

	class UserIsVerified extends BaseScrutinizerHandler {

		public function __construct (protected readonly AuthStorage $authStorage) {

			//
		}

		public function scrutinizeRequest ():void {

			$collector = end($this->metaFunnels);

			$columnName = $collector->verificationColumn;

			if (is_null($this->authStorage->getUser()->$columnName))

				throw new UnverifiedAccount($collector->verificationUrl);
		}
	}
?>