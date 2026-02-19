<?php

namespace Vadkuz\Flarum2Blog;

// Flarum classes
use Flarum\Api\Controller as FlarumController;
use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Filter\DiscussionFilterer;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Tags\Api\Serializer\TagSerializer;

// Controllers
use Vadkuz\Flarum2Blog\Controller\BlogOverviewController;
use Vadkuz\Flarum2Blog\Controller\BlogItemController;
use Vadkuz\Flarum2Blog\Controller\BlogComposerController;

// Access
use Vadkuz\Flarum2Blog\Access\ScopeDiscussionVisibility;
// API controllers
use Vadkuz\Flarum2Blog\Api\AttachForumSerializerAttributes;
use Vadkuz\Flarum2Blog\Api\AttachTagSerializerAttributes;
use Vadkuz\Flarum2Blog\Api\Controller\CreateBlogMetaController;
use Vadkuz\Flarum2Blog\Api\Controller\UpdateBlogMetaController;
use Vadkuz\Flarum2Blog\Api\Controller\UploadDefaultBlogImageController;
use Vadkuz\Flarum2Blog\Api\Controller\DeleteDefaultBlogImageController;
use Vadkuz\Flarum2Blog\Api\Serializer\BlogMetaSerializer;
// Listeners
use Vadkuz\Flarum2Blog\Listeners\CreateBlogMetaOnDiscussionCreate;

// Models
use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;

// Filters
use Vadkuz\Flarum2Blog\Query\FilterDiscussionsForBlogPosts;
use Vadkuz\Flarum2Blog\Query\BlogArticleFilterGambit;

// SEO
use Vadkuz\Flarum2Blog\SeoPage\SeoBlogOverviewMeta;
use Vadkuz\Flarum2Blog\SeoPage\SeoBlogArticleMeta;
use Vadkuz\Flarum2Blog\Subscribers\SeoBlogSubscriber;

$extend = [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/Forum.less')
        ->route('/blog', 'blog.overview', BlogOverviewController::class)
        ->route('/blog/compose', 'blog.compose', BlogComposerController::class)
        ->route('/blog/category/{category}', 'blog.category', BlogOverviewController::class)
        // Match Flarum's default discussion slug routing: numeric id with optional slug.
        ->route('/blog/{id:\\d+(?:-[^/]*)?}', 'blog.post', BlogItemController::class)
    // Shall we add RSS?
    // ->get('/blog/rss.xml', 'blog.rss.xml', RSS::class)
    ,
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/Admin.less'),

    (new Extend\Routes('api'))
        ->post('/blogMeta', 'blog.meta', CreateBlogMetaController::class)
        ->patch('/blogMeta/{id}', 'blog.meta.edit', UpdateBlogMetaController::class)
        ->post('/blog_default_image', 'blog.default_image.upload', UploadDefaultBlogImageController::class)
        ->delete('/blog_default_image', 'blog.default_image.delete', DeleteDefaultBlogImageController::class),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Model(Discussion::class))
        ->hasOne('blogMeta', BlogMeta::class, 'discussion_id'),

    (new Extend\ModelVisibility(Discussion::class))
        ->scope(ScopeDiscussionVisibility::class),

    (new Extend\ApiController(FlarumController\CreateDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\ListDiscussionsController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\ShowDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiController(FlarumController\UpdateDiscussionController::class))
        ->addInclude(['blogMeta', 'firstPost', 'user']),

    (new Extend\ApiSerializer(BasicDiscussionSerializer::class))
        ->hasOne('blogMeta', BlogMetaSerializer::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(AttachForumSerializerAttributes::class),

    (new Extend\ApiSerializer(TagSerializer::class))
        ->attributes(AttachTagSerializerAttributes::class),

    (new Extend\Filter(DiscussionFilterer::class))
        ->addFilterMutator(FilterDiscussionsForBlogPosts::class),

    (new Extend\SimpleFlarumSearch(DiscussionSearcher::class))
        ->addGambit(BlogArticleFilterGambit::class),
];

// Define events
$events = (new Extend\Event)
    ->listen(Saving::class, CreateBlogMetaOnDiscussionCreate::class);

// Extend Flarum SEO
if (class_exists("V17Development\FlarumSeo\Extend\SEO")) {
    $extend[] = (new \V17Development\FlarumSeo\Extend\SEO())
        ->addExtender("blog_category", SeoBlogOverviewMeta::class)
        ->addExtender("blog_article", SeoBlogArticleMeta::class);

    // Add Blog subscriber event
    $events->subscribe(SeoBlogSubscriber::class);
}

// Add events
$extend[] = $events;

return $extend;
