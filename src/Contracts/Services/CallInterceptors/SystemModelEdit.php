<?php

namespace Suphle\Contracts\Services\CallInterceptors;

interface SystemModelEdit extends ServiceErrorCatcher
{
    /**
     * @return Mixed. Perhaps, result of the update to any interested caller
    */
    public function updateModels(object $baseModel);

    /**
     * The rows to be locked while running the update. Should correspond to the rows of [updateModels]
    */
    public function modelsToUpdate (): array;
}
