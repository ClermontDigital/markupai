<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Models;

use DateTimeImmutable;
use MarkupAI\Models\StyleRewrite;
use PHPUnit\Framework\TestCase;

class StyleRewriteTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'rewritten_content' => 'This is the rewritten content.',
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleRewrite = StyleRewrite::fromArray($data);

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $styleRewrite->getId());
        $this->assertEquals('completed', $styleRewrite->getStatus());
        $this->assertEquals('This is the rewritten content.', $styleRewrite->getRewrittenContent());
        $this->assertInstanceOf(DateTimeImmutable::class, $styleRewrite->getCreatedAt());
    }

    public function testFromArrayWithNullContent(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleRewrite = StyleRewrite::fromArray($data);

        $this->assertNull($styleRewrite->getRewrittenContent());
        $this->assertFalse($styleRewrite->isCompleted());
    }

    public function testIsCompleted(): void
    {
        $completedRewrite = new StyleRewrite(
            '123',
            'completed',
            'content',
            new DateTimeImmutable()
        );

        $runningRewrite = new StyleRewrite(
            '456',
            'running',
            null,
            new DateTimeImmutable()
        );

        $this->assertTrue($completedRewrite->isCompleted());
        $this->assertFalse($runningRewrite->isCompleted());
    }

    public function testToArray(): void
    {
        $createdAt = new DateTimeImmutable('2025-01-20T14:30:00+00:00');
        $content = 'Rewritten content here.';

        $styleRewrite = new StyleRewrite(
            '123e4567-e89b-12d3-a456-426614174000',
            'completed',
            $content,
            $createdAt
        );

        $array = $styleRewrite->toArray();

        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'rewritten_content' => $content,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ], $array);
    }
}