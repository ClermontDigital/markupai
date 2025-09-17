<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit;

use MarkupAI\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $config = new Configuration('test-token');

        $this->assertEquals('test-token', $config->getToken());
        $this->assertEquals('https://api.markup.ai/v1', $config->getBaseUrl());
        $this->assertEquals(30, $config->getTimeout());
    }

    public function testConstructorWithCustomValues(): void
    {
        $config = new Configuration(
            'custom-token',
            'https://custom.api.com/v2',
            60
        );

        $this->assertEquals('custom-token', $config->getToken());
        $this->assertEquals('https://custom.api.com/v2', $config->getBaseUrl());
        $this->assertEquals(60, $config->getTimeout());
    }

    public function testBaseUrlTrimsTrailingSlash(): void
    {
        $config = new Configuration('token', 'https://api.markup.ai/v1/');

        $this->assertEquals('https://api.markup.ai/v1', $config->getBaseUrl());
    }

    public function testGetAuthorizationHeader(): void
    {
        $config = new Configuration('test-token');

        $this->assertEquals('Bearer test-token', $config->getAuthorizationHeader());
    }
}
