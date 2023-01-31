<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Response\Format\Reload;

	use Suphle\Contracts\Response\RendererManager;

	use Suphle\Services\Decorators\VariableDependencies;

	#[VariableDependencies([ "setRendererManager" ])]
	class ReloadHotwireStream extends BaseHotwireStream {

		public function __construct(protected string $handler) {

			$this->fallbackRenderer = new Reload($handler);
		}

		public function setRendererManager (RendererManager $rendererManager):void {
			
			$this->fallbackRenderer->setRendererManager($rendererManager);
		}
	}
?>