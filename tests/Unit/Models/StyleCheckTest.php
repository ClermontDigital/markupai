<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Models;

use DateTimeImmutable;
use MarkupAI\Models\StyleCheck;
use PHPUnit\Framework\TestCase;

class StyleCheckTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'results' => ['score' => 85, 'issues' => []],
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleCheck = StyleCheck::fromArray($data);

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $styleCheck->getId());
        $this->assertEquals('completed', $styleCheck->getStatus());
        $this->assertEquals(['score' => 85, 'issues' => []], $styleCheck->getResults());
        $this->assertInstanceOf(DateTimeImmutable::class, $styleCheck->getCreatedAt());
    }

    public function testFromArrayWithNullResults(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleCheck = StyleCheck::fromArray($data);

        $this->assertNull($styleCheck->getResults());
        $this->assertFalse($styleCheck->isCompleted());
    }

    public function testIsCompleted(): void
    {
        $completedCheck = new StyleCheck(
            '123',
            'completed',
            [],
            new DateTimeImmutable()
        );

        $runningCheck = new StyleCheck(
            '456',
            'running',
            null,
            new DateTimeImmutable()
        );

        $this->assertTrue($completedCheck->isCompleted());
        $this->assertFalse($runningCheck->isCompleted());
    }

    public function testToArray(): void
    {
        $createdAt = new DateTimeImmutable('2025-01-20T14:30:00+00:00');
        $results = ['score' => 85];

        $styleCheck = new StyleCheck(
            '123e4567-e89b-12d3-a456-426614174000',
            'completed',
            $results,
            $createdAt
        );

        $array = $styleCheck->toArray();

        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'results' => $results,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ], $array);
    }
}