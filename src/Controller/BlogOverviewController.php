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

        $q = "";

        // Add language support
        if ($this->extensionManager->isEnabled("fof-discussion-language")) {
            $q = "language:{$document->language} ";
        }

        $q .= "is:blog" . (Arr::get($queryParams, 'category') ? " tag:" . Arr::get($queryParams, 'category') : "");

        // Preload blog posts
        $apiDocument = $this->getApiDocument($request, [
            "filter" => [
                "q" => $q
            ],
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
