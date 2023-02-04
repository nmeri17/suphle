<?php
	namespace Suphle\Contracts\Requests;

	use Suphle\Request\RequestDetails;

	interface RequestEventsListener {

		public function handleRefreshEvent (RequestDetails $requestDetails):void;
	}
?>