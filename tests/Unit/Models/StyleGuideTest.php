<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Models;

use DateTimeImmutable;
use MarkupAI\Models\StyleGuide;
use PHPUnit\Framework\TestCase;

class StyleGuideTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Custom Marketing Guide',
            'created_at' => '2025-01-20T14:30:00+00:00',
            'created_by' => 'user123',
            'status' => 'completed',
        ];

        $styleGuide = StyleGuide::fromArray($data);

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $styleGuide->getId());
        $this->assertEquals('Custom Marketing Guide', $styleGuide->getName());
        $this->assertEquals('user123', $styleGuide->getCreatedBy());
        $this->assertEquals('completed', $styleGuide->getStatus());
        $this->assertInstanceOf(DateTimeImmutable::class, $styleGuide->getCreatedAt());
    }

    public function testToArray(): void
    {
        $createdAt = new DateTimeImmutable('2025-01-20T14:30:00+00:00');

        $styleGuide = new StyleGuide(
            '123e4567-e89b-12d3-a456-426614174000',
            'Custom Marketing Guide',
            $createdAt,
            'user123',
            'completed'
        );

        $array = $styleGuide->toArray();

        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Custom Marketing Guide',
            'created_at' => '2025-01-20T14:30:00+00:00',
            'created_by' => 'user123',
            'status' => 'completed',
        ], $array);
    }
}
