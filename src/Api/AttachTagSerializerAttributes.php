<?php

namespace V17Development\FlarumBlog\Api;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\Api\Serializer\TagSerializer;
use V17Development\FlarumBlog\Util\BlogTags;

class AttachTagSerializerAttributes
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
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

