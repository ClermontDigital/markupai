<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleSuggestion;
use MarkupAI\Resources\StyleSuggestions;
use PHPUnit\Framework\TestCase;

class StyleSuggestionsTest extends TestCase
{
    private Client $httpClient;

    private StyleSuggestions $styleSuggestions;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->styleSuggestions = new StyleSuggestions($this->httpClient);
    }

    public function testCreate(): void
    {
        $requestData = [
            'content' => 'Test content for suggestions',
            'style_guide_id' => 'guide-123',
        ];
        $responseData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'suggestions' => null,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('style/suggestions', $requestData)
            ->willReturn($responseData);

        $result = $this->styleSuggestions->create($requestData);

        $this->assertInstanceOf(StyleSuggestion::class, $result);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result->getId());
        $this->assertEquals('running', $result->getStatus());
    }

    public function testGet(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $responseData = [
            'id' => $id,
            'status' => 'completed',
            'suggestions' => [['type' => 'rewrite', 'text' => 'suggestion']],
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("style/suggestions/{$id}")
            ->willReturn($responseData);

        $result = $this->styleSuggestions->get($id);

        $this->assertInstanceOf(StyleSuggestion::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('completed', $result->getStatus());
    }
}