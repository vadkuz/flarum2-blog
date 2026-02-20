<?php

namespace Vadkuz\Flarum2Blog\Query;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Vadkuz\Flarum2Blog\Util\BlogTags;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class BlogArticleFilterGambit implements FilterInterface
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function getFilterKey(): string
    {
        return 'blog';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        $enabled = filter_var(Arr::first((array) $value), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($enabled === false) {
            return;
        }

        $tagIds = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));

        if (count($tagIds) === 0) {
            if (!$negate) {
                $state->getQuery()->whereRaw('1 = 0');
            }

            return;
        }

        $method = $negate ? 'whereNotIn' : 'whereIn';

        $state->getQuery()->{$method}('discussions.id', function (QueryBuilder $query) use ($tagIds) {
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
