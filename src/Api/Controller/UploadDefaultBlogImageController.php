<?php

namespace Vadkuz\Flarum2Blog\Api\Controller;

use Flarum\Api\Controller\UploadImageController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadDefaultBlogImageController extends UploadImageController
{
    protected string $filePathSettingKey = 'blog_default_image_path';
    protected string $filenamePrefix = 'blog_default_image';

    protected function makeImage(UploadedFileInterface $file): StreamInterface
    {
        return $file->getStream();
    }

    protected function fileExtension(ServerRequestInterface $request, UploadedFileInterface $file): string
    {
        $extension = strtolower(pathinfo((string) ($file->getClientFilename() ?? ''), PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'avif'];

        return in_array($extension, $allowed, true) ? $extension : 'png';
    }
}
