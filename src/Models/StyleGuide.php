<?php

declare(strict_types=1);

namespace MarkupAI\Models;

use DateTimeImmutable;

class StyleGuide
{
    private string $id;

    private string $name;

    private DateTimeImmutable $createdAt;

    private string $createdBy;

    private string $status;

    public function __construct(
        string $id,
        string $name,
        DateTimeImmutable $createdAt,
        string $createdBy,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->createdAt = $createdAt;
        $this->createdBy = $createdBy;
        $this->status = $status;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            new DateTimeImmutable($data['created_at']),
            $data['created_by'],
            $data['status']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->createdAt->format('c'),
            'created_by' => $this->createdBy,
            'status' => $this->status,
        ];
    }
}
