<?php

namespace Vadkuz\Flarum2Blog;

// Flarum classes
use Flarum\Extend;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;

// Controllers
use Vadkuz\Flarum2Blog\Controller\BlogOverviewController;
use Vadkuz\Flarum2Blog\Controller\BlogItemController;
use Vadkuz\Flarum2Blog\Controller\BlogComposerController;

// Access
use Vadkuz\Flarum2Blog\Access\ScopeDiscussionVisibility;
// API controllers
use Vadkuz\Flarum2Blog\Api\Controller\CreateBlogMetaController;
use Vadkuz\Flarum2Blog\Api\Controller\UpdateBlogMetaController;
use Vadkuz\Flarum2Blog\Api\Controller\UploadDefaultBlogImageController;
use Vadkuz\Flarum2Blog\Api\Controller\DeleteDefaultBlogImageController;
// Listeners
use Vadkuz\Flarum2Blog\Listeners\CreateBlogMetaOnDiscussionCreate;

// Models
use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;

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
