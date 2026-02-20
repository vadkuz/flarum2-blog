<?php

namespace Vadkuz\Flarum2Blog\Controller;

use Flarum\Frontend\Document;
use Flarum\Api\Client;
use Flarum\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Arr;

class BlogItemController
{
    public function __construct(protected Client $api)
    {
    }

    public function __invoke(Document $document, ServerRequestInterface $request): Document
    {
        $queryParams = $request->getQueryParams();
        $rawId = Arr::get($queryParams, 'id');
        $id = is_string($rawId) ? (int) explode('-', $rawId, 2)[0] : (int) $rawId;

        if ($id < 1) {
            throw new RouteNotFoundException();
        }

        $document->payload['apiDocument'] = $this->getApiDocument($request, $id);

        return $document;
    }

    /**
     * Preload blog posts
     *
     * @param ServerRequestInterface $request
     * @param int $id
     *
     * @return object
     */
    private function getApiDocument(ServerRequestInterface $request, int $id): object
    {
        $response = $this->api
            ->withoutErrorHandling()
            ->withParentRequest($request)
            ->get("/discussions/{$id}");

        if ($response->getStatusCode() === 404) {
            throw new RouteNotFoundException();
        }

        return json_decode($response->getBody(), false);
    }
}
