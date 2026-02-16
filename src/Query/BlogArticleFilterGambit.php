<?php

namespace V17Development\FlarumBlog\Query;

use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Query\Builder;
use V17Development\FlarumBlog\Util\BlogTags;

class BlogArticleFilterGambit extends AbstractRegexGambit
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
        // Get Flarum settings
        $this->settings = $settings;
    }

    protected function getGambitPattern()
    {
        return 'is:blog';
    }

    protected function conditions(SearchState $search, array $matches, $negate)
    {
        $tagIds = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));

        // If no blog tags are configured, `is:blog` should match nothing.
        // If negated (`-is:blog`), it should match everything.
        if (count($tagIds) === 0) {
            if (!$negate) {
                $search->getQuery()->whereRaw('1 = 0');
            }

            return;
        }

        $method = $negate ? 'whereNotIn' : 'whereIn';

        $search->getQuery()->{$method}('discussions.id', function (Builder $query) use ($tagIds) {
            $query->select('discussion_id')
                ->from('discussion_tag')
                ->whereIn('tag_id', $tagIds);
        });
    }
}
