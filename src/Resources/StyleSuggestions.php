<?php

declare(strict_types=1);

namespace MarkupAI\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleSuggestion;

class StyleSuggestions
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function create(array $data): StyleSuggestion
    {
        $response = $this->httpClient->post('style/suggestions', $data);

        return StyleSuggestion::fromArray($response);
    }

    public function createWithFile(array $data, string $filePath): StyleSuggestion
    {
        // Style suggestions accept txt, pdf, and md files according to API documentation
        $response = $this->httpClient->postWithFile('style/suggestions', $data, $filePath, ['txt', 'pdf', 'md']);

        return StyleSuggestion::fromArray($response);
    }

    public function get(string $id): StyleSuggestion
    {
        $response = $this->httpClient->get("style/suggestions/{$id}");

        return StyleSuggestion::fromArray($response);
    }
}
