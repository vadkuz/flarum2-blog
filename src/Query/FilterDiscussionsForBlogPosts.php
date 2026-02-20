<?php

namespace Vadkuz\Flarum2Blog\Query;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\SearchCriteria;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Vadkuz\Flarum2Blog\Util\BlogTags;

class FilterDiscussionsForBlogPosts
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(DatabaseSearchState $state, SearchCriteria $criteria): void
    {
        if (filter_var($this->settings->get('blog_filter_discussion_list'), FILTER_VALIDATE_BOOLEAN) === false) {
            return;
        }

        $hideBlogPosts = true;

        if ($state->isFulltextSearch()) {
            $hideBlogPosts = false;
        }

        foreach ($state->getActiveFilters() as $filter) {
            if ($filter instanceof BlogArticleFilterGambit) {
                $hideBlogPosts = false;
                break;
            }
        }

        if (!$hideBlogPosts) {
            return;
        }

        $tagIds = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));

        if (count($tagIds) === 0) {
            return;
        }

        $state
            ->getQuery()
            ->whereNotIn('discussions.id', function (QueryBuilder $query) use ($tagIds) {
                $query->select('discussion_id')
                    ->from('discussion_tag')
                    ->whereIn('tag_id', function (QueryBuilder $subQuery) use ($tagIds) {
                        $subQuery->select('id')
                            ->from('tags')
                            ->whereIn('id', $tagIds)
                            ->orWhereIn('parent_id', $tagIds);
                    });
            });
    }
}
