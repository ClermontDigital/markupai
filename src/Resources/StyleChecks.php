<?php

declare(strict_types=1);

namespace MarkupAI\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleCheck;

class StyleChecks
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function create(array $data): StyleCheck
    {
        $response = $this->httpClient->post('style-checks', $data);

        return StyleCheck::fromArray($response);
    }

    public function createWithFile(array $data, string $filePath): StyleCheck
    {
        // Style checks accept txt, pdf, and md files according to API documentation
        $response = $this->httpClient->postWithFile('style-checks', $data, $filePath, ['txt', 'pdf', 'md']);

        return StyleCheck::fromArray($response);
    }

    public function get(string $id): StyleCheck
    {
        $response = $this->httpClient->get("style-checks/{$id}");

        return StyleCheck::fromArray($response);
    }
}
