<?php

namespace Suphle\IO\Image;

use Suphle\Contracts\IO\Image\{ThumbnailOperationHandler, InferiorOperationHandler};

use Suphle\Services\Decorators\VariableDependencies;

use Suphle\IO\Image\Jobs\AsyncImageProcessor;

use Suphle\Queues\AdapterManager;

use Suphle\Exception\Explosives\DevError\UnmodifiedImageException;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Doesn't implement an interface since we don't intend to replace it. Doing so means it can be replaced with an implementation that permits save without a operation
*/
#[VariableDependencies([ "setInferiorImage", "setThumbnailImage" ])]
class OptimizersManager
{
    protected array $operations = [];
    protected array $originalImages;

    protected ThumbnailOperationHandler $thumbnailImage;

    protected InferiorOperationHandler $inferiorImage;

    protected string $imageResourceName;

    public function __construct(
        protected readonly AdapterManager $queueManager
    ) {

        //
    }

    public function setThumbnailImage(ThumbnailOperationHandler $thumbnailImage): void
    {

        $this->thumbnailImage = $thumbnailImage;
    }

    public function setInferiorImage(InferiorOperationHandler $inferiorImage): void
    {

        $this->inferiorImage = $inferiorImage;
    }

    /**
     * @return [inferior => [img1.png]]
    */
    final public function savedImageNames(): array
    {

        if (empty($this->operations)) {

            throw new UnmodifiedImageException();
        }

        $newImageNames = [];

        foreach ($this->operations as $operation) {

            $operation->setFiles($this->originalImages);

            $operation->setResourceName($this->imageResourceName);

            $operationName = $operation->getOperationName();

            if (!$operation->savesAsync()) {

                $newImageNames[$operationName] = $operation->getTransformed();
            } else {

                $newImageNames[$operationName] = $operation->getAsyncNames();

                $this->queueManager->addTask(
                    AsyncImageProcessor::class,
                    compact("operation")
                );
            }
        }

        $this->deleteTmpImages();

        return $newImageNames;
    }

    protected function deleteTmpImages(): void
    {

        foreach ($this->originalImages as $image) {

            unlink($image->getPathname());
        }
    }

    /**
     * @param {images} UploadedFile[]
    */
    public function setImages(array $images, string $resourceName): self
    {

        $this->originalImages = $images;

        $this->imageResourceName = $resourceName;

        return $this;
    }

    public function inferior(int $maxSize): self
    {

        $this->inferiorImage->setMaxSize($maxSize);

        $this->operations[] = $this->inferiorImage;

        return $this;
    }

    public function thumbnail(int $width, int $height): self
    {

        $this->thumbnailImage->setDimensions($width, $height);

        $this->operations[] = $this->thumbnailImage;

        return $this;
    }
}
