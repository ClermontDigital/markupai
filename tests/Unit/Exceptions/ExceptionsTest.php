<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Exceptions;

use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\MarkupAiException;
use MarkupAI\Exceptions\NotFoundException;
use MarkupAI\Exceptions\RateLimitException;
use MarkupAI\Exceptions\ServerException;
use MarkupAI\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public function testMarkupAiExceptionWithContext(): void
    {
        $context = ['key' => 'value', 'status' => 400];
        $exception = new MarkupAiException('Test message', 400, null, $context);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals($context, $exception->getContext());
    }

    public function testAuthenticationException(): void
    {
        $exception = new AuthenticationException('Invalid token', 401);

        $this->assertInstanceOf(MarkupAiException::class, $exception);
        $this->assertEquals('Invalid token', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function testValidationException(): void
    {
        $exception = new ValidationException('Validation failed', 422);

        $this->assertInstanceOf(MarkupAiException::class, $exception);
        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
    }

    public function testNotFoundException(): void
    {
        $exception = new NotFoundException('Resource not found', 404);

        $this->assertInstanceOf(MarkupAiException::class, $exception);
        $this->assertEquals('Resource not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    public function testRateLimitException(): void
    {
        $exception = new RateLimitException('Rate limit exceeded', 429);

        $this->assertInstanceOf(MarkupAiException::class, $exception);
        $this->assertEquals('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
    }

    public function testServerException(): void
    {
        $exception = new ServerException('Internal server error', 500);

        $this->assertInstanceOf(MarkupAiException::class, $exception);
        $this->assertEquals('Internal server error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }
}