<?php

namespace Vadkuz\Flarum2Blog\Api\Controller;

use Flarum\Api\JsonApiResponse;
use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;
use Vadkuz\Flarum2Blog\BlogMeta\Commands\UpdateBlogMeta;

class UpdateBlogMetaController implements RequestHandlerInterface
{
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        /** @var BlogMeta $blogMeta */
        $blogMeta = $this->bus->dispatch(
            new UpdateBlogMeta(RequestUtil::getActor($request), Arr::get($queryParams, 'id'), Arr::get($request->getParsedBody(), 'data', []))
        );

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
