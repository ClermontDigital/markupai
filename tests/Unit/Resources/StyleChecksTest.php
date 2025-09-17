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

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Style checks require file upload. Use createWithFile() method instead.');

        $this->styleChecks->create($requestData);
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
            ->with("style/checks/{$id}")
            ->willReturn($responseData);

        $result = $this->styleChecks->get($id);

        $this->assertInstanceOf(StyleCheck::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('completed', $result->getStatus());
    }
}