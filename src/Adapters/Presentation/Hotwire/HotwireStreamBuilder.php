<?php

namespace Suphle\Adapters\Presentation\Hotwire;

class HotwireStreamBuilder
{
    protected ?string $nodeContent = null;

    public function __construct(
        public readonly string $hotwireAction,
        public readonly string $targets
    ) {

        //
    }

    public function wrapContent(?string $nodeContent): self
    {

        $this->nodeContent = $nodeContent;

        return $this;
    }

    public function __toString(): string
    {

        $wrappedContent = !is_null($this->nodeContent) ?

            "<template>{$this->nodeContent}</template>" : "";

        return "<turbo-stream action='{$this->hotwireAction}' targets='{$this->targets}'>

				$wrappedContent
			</turbo-stream>";
    }
}
