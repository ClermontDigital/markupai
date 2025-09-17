<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Models;

use DateTimeImmutable;
use MarkupAI\Models\StyleSuggestion;
use PHPUnit\Framework\TestCase;

class StyleSuggestionTest extends TestCase
{
    public function testFromArray(): void
    {
        $suggestions = [
            ['type' => 'rewrite', 'original' => 'test', 'suggested' => 'better test']
        ];

        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'suggestions' => $suggestions,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleSuggestion = StyleSuggestion::fromArray($data);

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $styleSuggestion->getId());
        $this->assertEquals('completed', $styleSuggestion->getStatus());
        $this->assertEquals($suggestions, $styleSuggestion->getSuggestions());
        $this->assertInstanceOf(DateTimeImmutable::class, $styleSuggestion->getCreatedAt());
    }

    public function testFromArrayWithNullSuggestions(): void
    {
        $data = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'running',
            'created_at' => '2025-01-20T14:30:00+00:00',
        ];

        $styleSuggestion = StyleSuggestion::fromArray($data);

        $this->assertNull($styleSuggestion->getSuggestions());
        $this->assertFalse($styleSuggestion->isCompleted());
    }

    public function testIsCompleted(): void
    {
        $completedSuggestion = new StyleSuggestion(
            '123',
            'completed',
            [],
            new DateTimeImmutable()
        );

        $runningSuggestion = new StyleSuggestion(
            '456',
            'running',
            null,
            new DateTimeImmutable()
        );

        $this->assertTrue($completedSuggestion->isCompleted());
        $this->assertFalse($runningSuggestion->isCompleted());
    }

    public function testToArray(): void
    {
        $createdAt = new DateTimeImmutable('2025-01-20T14:30:00+00:00');
        $suggestions = [['type' => 'rewrite']];

        $styleSuggestion = new StyleSuggestion(
            '123e4567-e89b-12d3-a456-426614174000',
            'completed',
            $suggestions,
            $createdAt
        );

        $array = $styleSuggestion->toArray();

        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'status' => 'completed',
            'suggestions' => $suggestions,
            'created_at' => '2025-01-20T14:30:00+00:00',
        ], $array);
    }
}