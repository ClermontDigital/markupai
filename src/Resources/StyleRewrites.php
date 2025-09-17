<?php

declare(strict_types=1);

namespace MarkupAI\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleRewrite;

class StyleRewrites
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function create(array $data): StyleRewrite
    {
        $response = $this->httpClient->post('style/rewrites', $data);

        return StyleRewrite::fromArray($response);
    }

    public function createWithFile(array $data, string $filePath): StyleRewrite
    {
        // Style rewrites accept txt, pdf, and md files according to API documentation
        $response = $this->httpClient->postWithFile('style/rewrites', $data, $filePath, ['txt', 'pdf', 'md']);

        return StyleRewrite::fromArray($response);
    }

    public function get(string $id): StyleRewrite
    {
        $response = $this->httpClient->get("style/rewrites/{$id}");

        return StyleRewrite::fromArray($response);
    }
}
