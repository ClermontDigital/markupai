<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleCheck;
use MarkupAI\Resources\StyleChecks;
use PHPUnit\Framework\TestCase;

class StyleChecksTest extends TestCase
{
    private Client $httpClient;

    private StyleChecks $styleChecks;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->styleChecks = new StyleChecks($this->httpClient);
    }

    public function testCreate(): void
    {
        $requestData = [
            'content' => 'Test content',
            'style_guide_id' => 'guide-123',
        ];
        $responseData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'results' => null,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('style-checks', $requestData)
            ->willReturn($responseData);

        $result = $this->styleChecks->create($requestData);

        $this->assertInstanceOf(StyleCheck::class, $result);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result->getId());
        $this->assertEquals('running', $result->getStatus());
    }

    public function testGet(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $responseData = [
            'id' => $id,
            'status' => 'completed',
            'results' => ['score' => 85],
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("style-checks/{$id}")
            ->willReturn($responseData);

        $result = $this->styleChecks->get($id);

        $this->assertInstanceOf(StyleCheck::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('completed', $result->getStatus());
    }
}