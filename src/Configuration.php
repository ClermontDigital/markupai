<?php

declare(strict_types=1);

namespace MarkupAI;

class Configuration
{
    private string $token;

    private string $baseUrl;

    private int $timeout;

    public function __construct(string $token, string $baseUrl = 'https://api.markup.ai/v1', int $timeout = 30)
    {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getAuthorizationHeader(): string
    {
        return 'Bearer ' . $this->token;
    }
}
