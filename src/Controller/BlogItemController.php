<?php

namespace V17Development\FlarumBlog\Controller;

use Flarum\Frontend\Document;
use Flarum\Api\Client;
use Flarum\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Arr;

class BlogItemController
{
    /**
     * @var Client
     */
    protected $api;

    /**
    public function __construct(Client $api)
    {
        $this->api = $api;
    }

    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();

        // Find blog item
        $apiDocument = $this->getApiDocument($request, (int) Arr::get($queryParams, 'id'));

        // Article not found
        if ($apiDocument === null) {
            return $document;
        }

        // Set payload
        $document->payload['apiDocument'] = $apiDocument;

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
    private function getApiDocument(ServerRequestInterface $request, $id)
    {
        $response = $this->api->withParentRequest($request)->get("/discussions/{$id}");

        if ($response->getStatusCode() === 404) {
            throw new RouteNotFoundException();
        }

        return json_decode($response->getBody());
    }
}
