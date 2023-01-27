<?php
	namespace Suphle\Adapters\Presentation\Hotwire;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Adapters\Presentation\Hotwire\Formats\BaseHotwireStream;

	trait HotwireAsserter {

		/**
		 * Doesn't confirm element presence within the frame since we have no APIs for drilling through DOM trees
		*/
		protected function assertStreamNode (string $hotwireAction, ?string $target = null):void {

			$renderer = $this->getContainer()->getClass(BaseRenderer::class);

			if (!$renderer instanceof BaseHotwireStream)

				$this->fail(BaseHotwireStream::class." not found");

			foreach ($renderer->getStreamBuilders() as $builder) {

				if ($builder->hotwireAction == $hotwireAction) {

					if (is_null($target)) return $this->assertTrue(true);

					if ($builder->target == $target)

						 return $this->assertTrue(true);
				}
			}

			$failMessage = "No node found in response matching action '$hotwireAction'";

			if (!is_null($target))

				$failMessage .= " and target '$target'";

			$this->fail($failMessage);
		}
	}
?>