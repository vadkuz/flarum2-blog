<?php

namespace Vadkuz\Flarum2Blog\Controller;

use Flarum\Frontend\Document;
use Flarum\Api\Client;
use Flarum\Extension\ExtensionManager;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Arr;

class BlogOverviewController
{
    /**
     * @var Client
     */
    protected $api;

    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    public function __construct(Client $api, ExtensionManager $extensionManager)
    {
        $this->api = $api;
        $this->extensionManager = $extensionManager;
    }

    public function __invoke(Document $document, ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $filter = [
            'blog' => '1',
        ];

        if ($this->extensionManager->isEnabled("fof-discussion-language")) {
            $filter['language'] = $document->language;
        }

        if (Arr::get($queryParams, 'category')) {
            $filter['tag'] = Arr::get($queryParams, 'category');
        }

        // Preload blog posts
        $apiDocument = $this->getApiDocument($request, [
            "filter" => $filter,
            "sort" => "-createdAt"
        ]);

        // Set payload
        $document->payload['apiDocument'] = $apiDocument;

        return $document;
    }

    /**
     * Preload blog posts
     *
     * @param ServerRequestInterface $request
     * @param array $params
     *
     * @return object
     */
    private function getApiDocument(ServerRequestInterface $request, array $params)
    {
        return json_decode($this->api->withParentRequest($request)->withQueryParams($params)->get('/discussions')->getBody());
    }
}
