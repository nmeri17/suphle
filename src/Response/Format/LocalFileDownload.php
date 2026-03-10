<?php

namespace Suphle\Response\Format;

use Suphle\Request\PayloadStorage;
use Suphle\Contracts\Response\OpenApiRenderer;
use Suphle\Response\Traits\OpenApiRendererTrait;

use Symfony\Component\HttpFoundation\File\{File, Exception\FileException};

use Closure;

class LocalFileDownload extends Redirect implements OpenApiRenderer
{
    use OpenApiRendererTrait;

    public const STATUS_CODE = 200;

    public function __construct(
        protected string $handler,
        protected Closure $deriveFilePath,
        protected ?Closure $fallbackRedirect = null
    ) {
    }

    protected function serializableProperties(): array
    {

        return [ "deriveFilePath", "fallbackRedirect" ];
    }

    public function render(): string
    {

        $fileObject = $this->getFileObject();

        if (is_null($fileObject)) {
            return "";
        }

        $this->setDownloadHeaders($fileObject);

        return $fileObject->getContent();
    }

    /**
     * @throws FileException
    */
    protected function getFileObject(): ?File
    {

        try {

            return new File(
                $this->callbackDetails->recursiveValueDerivation(
                    $this->deriveFilePath,
                    $this
                )
            );
        } catch (FileException $exception) {

            if (is_null($this->fallbackRedirect)) {
                throw $exception;
            }

            $this->statusCode = 404;

            $this->renderRedirect($this->fallbackRedirect);

            return null;
        }
    }

    protected function setDownloadHeaders(File $fileObject): void
    {

        $fileName = $fileObject->getFileName();

        $this->headers = array_merge($this->headers, [

            PayloadStorage::CONTENT_TYPE_KEY => $fileObject->getMimeType(),

            "Content-Disposition" => "attachment; filename='$fileName'",

            "Content-Length" => mb_strlen($fileObject->getContent()),

            "Connection" => "Keep-Alive"
        ]);
    }

    /**
     * Override default content type for LocalFileDownload
     */
    public static function getContentType(): string
    {
        return 'application/octet-stream';
    }

    /**
     * Override default response schema for LocalFileDownload
     */
    public static function getResponseSchema(): array
    {
        return [
            'type' => 'string',
            'format' => 'binary',
            'description' => static::getDescription(),
            'headers' => [
                'Content-Disposition' => [
                    'description' => 'File download attachment header',
                    'schema' => [
                        'type' => 'string'
                    ]
                ],
                'Content-Length' => [
                    'description' => 'File size in bytes',
                    'schema' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
    }

    /**
     * Override default description for LocalFileDownload
     */
    public static function getDescription(): string
    {
        return 'File download response';
    }
}
