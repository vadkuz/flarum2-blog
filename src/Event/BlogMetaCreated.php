<?php

namespace Vadkuz\Flarum2Blog\Event;

use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;

class BlogMetaCreated
{
    /**
     * @var BlogMeta
     */
    public $blogMeta;

    public function __construct(BlogMeta $blogMeta)
    {
        $this->blogMeta = $blogMeta;
    }
}
