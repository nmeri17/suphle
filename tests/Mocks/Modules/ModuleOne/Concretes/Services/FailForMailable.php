<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Exception\ShutdownAlerters\MailBuildAlerter;

	class FailForMailable {

		public function __construct(protected readonly MailBuildAlerter $dependency) {

			//
		}
	}
?>