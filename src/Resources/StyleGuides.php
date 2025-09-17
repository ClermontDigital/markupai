<?php

declare(strict_types=1);

namespace MarkupAI\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleGuide;

class StyleGuides
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function list(): array
    {
        $response = $this->httpClient->get('style-guides');

        return array_map(
            fn (array $item) => StyleGuide::fromArray($item),
            $response
        );
    }

    public function create(array $data): StyleGuide
    {
        $response = $this->httpClient->post('style-guides', $data);

        return StyleGuide::fromArray($response);
    }

    public function createWithFile(array $data, string $filePath): StyleGuide
    {
        // Style guides only accept PDF files according to API documentation
        $response = $this->httpClient->postWithFile('style-guides', $data, $filePath, ['pdf']);

        return StyleGuide::fromArray($response);
    }

    public function get(string $id): StyleGuide
    {
        $response = $this->httpClient->get("style-guides/{$id}");

        return StyleGuide::fromArray($response);
    }

    public function update(string $id, array $data): StyleGuide
    {
        $response = $this->httpClient->patch("style-guides/{$id}", $data);

        return StyleGuide::fromArray($response);
    }

    public function delete(string $id): void
    {
        $this->httpClient->delete("style-guides/{$id}");
    }
}
