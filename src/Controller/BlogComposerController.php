<?php

namespace Vadkuz\Flarum2Blog\Controller;

use Flarum\Frontend\Document;
use Psr\Http\Message\ServerRequestInterface;

class BlogComposerController
{
    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        return $document;
    }
}