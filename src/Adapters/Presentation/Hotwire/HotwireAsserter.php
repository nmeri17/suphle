<?php
	namespace Suphle\Adapters\Presentation\Hotwire;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream};

	use Suphle\Testing\Proxies\Extensions\TestResponseBridge;

	trait HotwireAsserter {

		/**
		 * Doesn't confirm element presence within the frame since we have no APIs for drilling through DOM trees
		*/
		protected function assertStreamNode (string $hotwireAction, ?string $targets = null):void {

			$renderer = $this->getContainer()->getClass(BaseRenderer::class);

			if (!$renderer instanceof BaseHotwireStream)

				$this->fail(BaseHotwireStream::class." not found");

			foreach ($renderer->getStreamBuilders() as $builder) {

				if ($builder->hotwireAction == $hotwireAction) {

					if (is_null($targets)) {

						$this->assertTrue(true);

						return;
					}

					if ($builder->targets == $targets) {

						$this->assertTrue(true);

						return;
					}
				}
			}

			$failMessage = "No node found in response matching action '$hotwireAction'";

			if (!is_null($targets))

				$failMessage .= " and target '$targets'";

			$this->fail($failMessage);
		}

		protected function assertHotwireRedirect (TestResponseBridge $response):void {

			$response->assertStatus(RedirectHotwireStream::STATUS_CODE);
		}
	}
?>