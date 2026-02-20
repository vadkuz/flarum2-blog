<?php

namespace Vadkuz\Flarum2Blog\BlogMeta;

use Flarum\Foundation\AbstractValidator;

class BlogMetaValidator extends AbstractValidator
{
    protected array $rules = [
        'featured_image' => ['string', 'nullable'],
        'summary' => ['string', 'nullable'],
        'is_featured' => ['boolean'],
        'is_sized' => ['boolean'],
        'is_pending_review' => ['boolean']
    ];
}
