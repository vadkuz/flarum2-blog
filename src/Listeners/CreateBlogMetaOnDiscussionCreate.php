<?php

namespace V17Development\FlarumBlog\Listeners;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\Discussion\Event\Saving;
use Flarum\Foundation\DispatchEventsTrait;
use V17Development\FlarumBlog\BlogMeta\BlogMeta;
use Illuminate\Support\Arr;
use V17Development\FlarumBlog\Util\BlogTags;

class CreateBlogMetaOnDiscussionCreate
{
    use DispatchEventsTrait;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * CreateBlogMetaOnDiscussionCreate constructor.
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        SettingsRepositoryInterface $settings,
        Dispatcher $events
    ) {
        // Get Flarum settings
        $this->settings = $settings;
        $this->events = $events;
        $this->blogTags = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));
    }

    /**
     * @param $event
     */
    public function handle(Saving $event)
    {
        $discussion = $event->discussion;

        // Only add blog meta data if the discussion does not exists yet
        if ($discussion->exists) {
            return;
        }

        // If the incoming payload contains a blog tag, validate permissions before we persist anything.
        // This avoids "create discussion, then fail afterSave" behavior.
        $incomingTagIds = [];
        foreach ((array) Arr::get($event->data, 'relationships.tags.data', []) as $tag) {
            $id = (string) Arr::get($tag, 'id', '');
            if ($id !== '' && ctype_digit($id)) {
                $incomingTagIds[] = (int) $id;
            }
        }
        $incomingTagIds = array_values(array_unique($incomingTagIds));

        if (count($this->blogTags) > 0 && count(array_intersect($incomingTagIds, $this->blogTags)) > 0) {
            if (!$event->actor->can('blog.writeArticles')) {
                throw new PermissionDeniedException;
            }
        }

        // After the tags are synced, check if it's a blog article
        $discussion->afterSave(function ($discussion) use ($event) {

            // Here it may happen that `$discussion->tags` gives an empty array because of a strange bug.
            // This can be reproduced when using the fof/discussion-language extension (v1.2.1)
            // For this reason we need to explicitly reload the tags relationship before using it here.
            $discussion->load('tags');

            // Make sure it's a blog base discussion!
            if (count($this->blogTags) > 0 && $discussion->tags && $discussion->tags->whereIn('id', $this->blogTags)->count() > 0) {
                if (!$event->actor->can('blog.writeArticles')) {
                    throw new PermissionDeniedException;
                }

                // Auto approve if it does not require a review
                $isPendingReview = $this->settings->get('blog_requires_review', false) == true && !$event->actor->can('blog.autoApprovePosts');

                $blogMeta = BlogMeta::build(
                    $discussion->id,
                    Arr::get($event->data, 'attributes.blogMeta.featuredImage', null),
                    Arr::get($event->data, 'attributes.blogMeta.summary', null),
                    Arr::get($event->data, 'attributes.blogMeta.isFeatured', false),
                    Arr::get($event->data, 'attributes.blogMeta.isSized', false),
                    $isPendingReview
                );

                // Save meta
                $blogMeta->save();

                $this->dispatchEventsFor($blogMeta, $event->actor);

                // Autolock articles
                if ($this->settings->get('blog_allow_comments', true) == false) {
                    $discussion->is_locked = true;
                    $discussion->save();
                }
            }
        });
    }
}
