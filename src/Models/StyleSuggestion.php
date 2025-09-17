<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleSuggestion
{
    private string $id;

    private string $status;

    private ?array $suggestions;

    private ?DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $status,
        ?array $suggestions,
        ?DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->suggestions = $suggestions;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        // Handle both 'id' and 'workflow_id' from API responses
        $id = $data['id'] ?? $data['workflow_id'];

        $createdAt = null;
        if (isset($data['created_at'])) {
            $createdAt = new DateTimeImmutable($data['created_at']);
        }

        return new self(
            $id,
            $data['status'],
            $data['suggestions'] ?? null,
            $createdAt
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

    public function getCreatedAt(): ?DateTimeImmutable
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
            'created_at' => $this->createdAt?->format('c'),
        ];
    }
}
