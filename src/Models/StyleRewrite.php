<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleRewrite
{
    private string $id;

    private string $status;

    private ?string $rewrittenContent;

    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $status,
        ?string $rewrittenContent,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->rewrittenContent = $rewrittenContent;
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['status'],
            $data['rewritten_content'] ?? null,
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

    public function getRewrittenContent(): ?string
    {
        return $this->rewrittenContent;
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
            'rewritten_content' => $this->rewrittenContent,
            'created_at' => $this->createdAt->format('c'),
        ];
    }
}
