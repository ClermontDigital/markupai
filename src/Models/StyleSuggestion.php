<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleSuggestion
{
    private string $id;

    private string $status;

    private ?array $suggestions;

    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $status,
        ?array $suggestions,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->suggestions = $suggestions;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['status'],
            $data['suggestions'] ?? null,
            new DateTimeImmutable($data['created_at'])
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSuggestions(): ?array
    {
        return $this->suggestions;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'suggestions' => $this->suggestions,
            'created_at' => $this->createdAt->format('c'),
        ];
    }
}
