<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleCheck
{
    private string $id;

    private string $status;

    private ?array $results;

    private ?DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $status,
        ?array $results,
        ?DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->results = $results;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        // Handle different response structures from API
        if (isset($data['workflow'])) {
            // GET response has nested workflow structure
            $workflow = $data['workflow'];
            $id = $workflow['id'];
            $status = $workflow['status'];

            // For completed workflows, results are in original, config, etc.
            $results = null;
            if ($status === 'completed' && isset($data['original'])) {
                $results = [
                    'original' => $data['original'],
                    'config' => $data['config'] ?? null
                ];
            }
        } else {
            // POST response has flat structure
            $id = $data['id'] ?? $data['workflow_id'];
            $status = $data['status'];
            $results = $data['results'] ?? null;
        }

        $createdAt = null;
        if (isset($data['created_at'])) {
            $createdAt = new DateTimeImmutable($data['created_at']);
        } elseif (isset($data['workflow']['generated_at'])) {
            $createdAt = new DateTimeImmutable($data['workflow']['generated_at']);
        }

        return new self(
            $id,
            $status,
            $results,
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

    public function getResults(): ?array
    {
        return $this->results;
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
            'results' => $this->results,
            'created_at' => $this->createdAt?->format('c'),
        ];
    }
}
