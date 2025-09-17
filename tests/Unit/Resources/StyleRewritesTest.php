<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleRewrite;
use MarkupAI\Resources\StyleRewrites;
use PHPUnit\Framework\TestCase;

class StyleRewritesTest extends TestCase
{
    private Client $httpClient;

    private StyleRewrites $styleRewrites;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->styleRewrites = new StyleRewrites($this->httpClient);
    }

    public function testCreate(): void
    {
        $requestData = [
            'content' => 'Content to rewrite',
            'style_guide_id' => 'guide-123',
            'tone' => 'professional',
        ];
        $responseData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'rewritten_content' => null,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('style-rewrites', $requestData)
            ->willReturn($responseData);

        $result = $this->styleRewrites->create($requestData);

        $this->assertInstanceOf(StyleRewrite::class, $result);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result->getId());
        $this->assertEquals('running', $result->getStatus());
    }

    public function testGet(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $responseData = [
            'id' => $id,
            'status' => 'completed',
            'rewritten_content' => 'This is the professionally rewritten content.',
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("style-rewrites/{$id}")
            ->willReturn($responseData);

        $result = $this->styleRewrites->get($id);

        $this->assertInstanceOf(StyleRewrite::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('completed', $result->getStatus());
        $this->assertEquals('This is the professionally rewritten content.', $result->getRewrittenContent());
    }
}