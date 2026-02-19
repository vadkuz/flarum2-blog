<?php

namespace Vadkuz\Flarum2Blog\Query;

use Flarum\Filter\FilterState;
use Flarum\Query\QueryCriteria;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Discussion\Search\Gambit\FulltextGambit;
use Vadkuz\Flarum2Blog\Util\BlogTags;

class FilterDiscussionsForBlogPosts
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * FilterDiscussionsForBlogPosts constructor.
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        // Get forum settings
        $this->settings = $settings;
	}
	
	/**
	 * @param FilterState $filter
	 * @param QueryCriteria $queryCriteria
	 */
	public function __invoke(FilterState $filter, QueryCriteria $queryCriteria)
	{
		// Do we need to filter?
		if(filter_var($this->settings->get('blog_filter_discussion_list'), FILTER_VALIDATE_BOOLEAN) === false) {
			return;
		}

		$activeGambits = $filter->getActiveFilters();
		$hideBlogPosts = true;

		// Loop through the active gambits
		foreach ($activeGambits as $gambit) {
			if(get_class($gambit) === BlogArticleFilterGambit::class) {
				$hideBlogPosts = false;
			}
			if(get_class($gambit) === FulltextGambit::class) {
				$hideBlogPosts = false;
			}
		}

		// Filter discussions from discussion list
		if($hideBlogPosts) {
			$tagIds = BlogTags::parseTagIds($this->settings->get('blog_tags', ''));

			if (count($tagIds) === 0) {
				return;
			}

			$filter
				->getQuery()
				->whereNotIn('discussions.id', function ($query) use ($tagIds) {
					$query->select('discussion_id')
						->from('discussion_tag')
						->whereIn('tag_id', $tagIds);
				});
		}
	}
}
