<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Response\Format\Reload;

	class ReloadHotwireStream extends BaseHotwireStream {

		public function __construct(string $handler) {

			$this->fallbackRenderer = new Reload($handler);
		}
	}
?>