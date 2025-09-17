<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleCheck
{
    private string $id;

    private string $status;

    private ?array $results;

    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $status,
        ?array $results,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->results = $results;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['status'],
            $data['results'] ?? null,
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

    public function getResults(): ?array
    {
        return $this->results;
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
            'results' => $this->results,
            'created_at' => $this->createdAt->format('c'),
        ];
    }
}
