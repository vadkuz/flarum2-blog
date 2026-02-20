<?php

namespace Vadkuz\Flarum2Blog\Api\Controller;

use Flarum\Api\JsonApiResponse;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vadkuz\Flarum2Blog\BlogMeta\BlogMeta;
use Vadkuz\Flarum2Blog\BlogMeta\Commands\CreateBlogMeta;
use Flarum\Http\RequestUtil;
use Psr\Http\Server\RequestHandlerInterface;

class CreateBlogMetaController implements RequestHandlerInterface
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
        /** @var BlogMeta $blogMeta */
        $blogMeta = $this->bus->dispatch(
            new CreateBlogMeta(RequestUtil::getActor($request), Arr::get($request->getParsedBody(), 'data', []))
        );

        return new JsonApiResponse([
            'data' => $this->serialize($blogMeta),
        ], 201);
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
