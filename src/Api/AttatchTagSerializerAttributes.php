<?php

namespace Vadkuz\Flarum2Blog\Api;

use Flarum\Tags\Api\Serializer\TagSerializer;
use Vadkuz\Flarum2Blog\Util\BlogTags;

class AttatchTagSerializerAttributes
{
    /**
     * @deprecated Use {@see AttachTagSerializerAttributes}. Kept for BC.
     */
    protected $settings;

    public function __construct(\Flarum\Settings\SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(TagSerializer $serializer, $model, $attributes)
    {
        $blogTagIds = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));

        $tagId = (int) $model->id;
        $parentId = $model->parent_id ? (int) $model->parent_id : null;

        $attributes['isBlog'] = in_array($tagId, $blogTagIds, true)
            || ($parentId !== null && in_array($parentId, $blogTagIds, true));

        return $attributes;
    }
}
