<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Resources;

use MarkupAI\Http\Client;
use MarkupAI\Models\StyleGuide;
use MarkupAI\Resources\StyleGuides;
use PHPUnit\Framework\TestCase;

class StyleGuidesTest extends TestCase
{
    private Client $httpClient;

    private StyleGuides $styleGuides;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $this->styleGuides = new StyleGuides($this->httpClient);
    }

    public function testList(): void
    {
        $responseData = [
            [
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'name' => 'Custom Marketing Guide',
                'created_at' => '2025-01-20T14:30:00+00:00',
                'created_by' => 'user123',
                'status' => 'completed',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('style-guides')
            ->willReturn($responseData);

        $result = $this->styleGuides->list();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(StyleGuide::class, $result[0]);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result[0]->getId());
    }

    public function testCreate(): void
    {
        $requestData = ['name' => 'New Style Guide'];
        $responseData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'New Style Guide',
            'created_at' => '2025-01-20T14:30:00+00:00',
            'created_by' => 'user123',
            'status' => 'completed',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('style-guides', $requestData)
            ->willReturn($responseData);

        $result = $this->styleGuides->create($requestData);

        $this->assertInstanceOf(StyleGuide::class, $result);
        $this->assertEquals('New Style Guide', $result->getName());
    }

    public function testGet(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $responseData = [
            'id' => $id,
            'name' => 'Custom Marketing Guide',
            'created_at' => '2025-01-20T14:30:00+00:00',
            'created_by' => 'user123',
            'status' => 'completed',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("style-guides/{$id}")
            ->willReturn($responseData);

        $result = $this->styleGuides->get($id);

        $this->assertInstanceOf(StyleGuide::class, $result);
        $this->assertEquals($id, $result->getId());
    }

    public function testUpdate(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $requestData = ['name' => 'Updated Style Guide'];
        $responseData = [
            'id' => $id,
            'name' => 'Updated Style Guide',
            'created_at' => '2025-01-20T14:30:00+00:00',
            'created_by' => 'user123',
            'status' => 'completed',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('patch')
            ->with("style-guides/{$id}", $requestData)
            ->willReturn($responseData);

        $result = $this->styleGuides->update($id, $requestData);

        $this->assertInstanceOf(StyleGuide::class, $result);
        $this->assertEquals('Updated Style Guide', $result->getName());
    }

    public function testDelete(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';

        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with("style-guides/{$id}")
            ->willReturn([]);

        $this->styleGuides->delete($id);
    }
}
