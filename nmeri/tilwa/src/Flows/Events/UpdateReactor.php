<?php

	namespace Tilwa\Flows\Events;

	class UpdateReactor {

		// remember to mount this somewhere during app boot

		// we're listening for "refresh" events from dev's service of choice. emit the path (which we use as topic). the listener just has to refresh collections matching the pattern/topic

		// pub/sub will be more efficient [than tags]; hopefully, they don't loop through subscribers

		// write tests for it when done
	}
?>