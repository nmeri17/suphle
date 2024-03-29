<?php

namespace Suphle\Tests\Unit\Files;

use Suphle\Adapters\Image\Optimizers\NativeReducerClient;

use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\FilesystemCleaner};

use Suphle\Tests\Integration\Generic\CommonBinds;

class InferiorImageTest extends IsolatedComponentTest
{
    use FilesystemCleaner;
    use CommonBinds;

    public function test_file_size_does_reduce()
    {

        // given
        $picture = $this->saveFakeImage("nmeri.jpg", 100, 100, 300);

        $oldPath = $picture->getPathName();

        $newPath = $this->getNewPath();

        copy($oldPath, $newPath);

        $generatedPath = $this->container->getClass(NativeReducerClient::class)

        ->downgradeImage($picture, $newPath, 150); // when

        $sizeBefore = filesize($oldPath);

        $sizeAfter = filesize($generatedPath);

        $this->assertGreaterThan($sizeAfter, $sizeBefore); // then

        foreach ([$oldPath, $generatedPath] as $path) { // cleanup

            unlink($path);
        }
    }

    private function getNewPath(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR ."reduced.jpg";
    }
}
