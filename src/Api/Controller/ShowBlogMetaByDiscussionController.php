<?php

namespace Vadkuz\Flarum2Blog\Api\Controller;

use Flarum\Api\JsonApiResponse;
use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;

class ShowBlogMetaByDiscussionController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertCan('seo.canConfigure');

        $queryParams = $request->getQueryParams();
        $discussionId = (int) Arr::get($queryParams, 'id');

        $blogMeta = BlogMeta::query()
            ->where('discussion_id', $discussionId)
            ->firstOrFail();

        return new JsonApiResponse([
            'data' => $this->serialize($blogMeta),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(BlogMeta $blogMeta): array
    {
        return [
            'type' => 'blogMeta',
            'id' => (string) $blogMeta->id,
            'attributes' => [
                'featuredImage' => $blogMeta->featured_image,
                'summary' => $blogMeta->summary,
                'isFeatured' => (bool) $blogMeta->is_featured,
                'isSized' => (bool) $blogMeta->is_sized,
                'isPendingReview' => (bool) $blogMeta->is_pending_review,
            ],
            'relationships' => [
                'discussion' => [
                    'data' => [
                        'type' => 'discussions',
                        'id' => (string) $blogMeta->discussion_id,
                    ],
                ],
            ],
        ];
    }
}

