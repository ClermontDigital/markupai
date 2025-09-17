<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit;

use GuzzleHttp\Client as GuzzleHttpClient;
use MarkupAI\MarkupAiClient;
use MarkupAI\Resources\StyleChecks;
use MarkupAI\Resources\StyleGuides;
use MarkupAI\Resources\StyleRewrites;
use MarkupAI\Resources\StyleSuggestions;
use Nyholm\Psr7\Factory\Psr17Factory as NyholmPsr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MarkupAiClientTest extends TestCase
{
    public function testConstructorWithDefaults(): void
    {
        $client = new MarkupAiClient('test-token');

        $this->assertInstanceOf(StyleGuides::class, $client->styleGuides());
        $this->assertInstanceOf(StyleChecks::class, $client->styleChecks());
        $this->assertInstanceOf(StyleSuggestions::class, $client->styleSuggestions());
        $this->assertInstanceOf(StyleRewrites::class, $client->styleRewrites());
    }

    public function testConstructorWithCustomParameters(): void
    {
        $client = new MarkupAiClient(
            token: 'custom-token',
            baseUrl: 'https://custom.api.com/v2',
            timeout: 60
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testConstructorWithProvidedHttpDependencies(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $client = new MarkupAiClient(
            token: 'test-token',
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryWithPartialDependencies(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);

        // Test with only HTTP client provided - should auto-discover factories
        $client = new MarkupAiClient(
            token: 'test-token',
            httpClient: $httpClient
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryWithGuzzleAvailable(): void
    {
        // This tests the auto-discovery paths when Guzzle is available
        if (!class_exists(GuzzleHttpClient::class)) {
            $this->markTestSkipped('GuzzleHTTP not available');
        }

        $client = new MarkupAiClient('test-token');
        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryWithNyholmAvailable(): void
    {
        // This tests the auto-discovery paths when Nyholm PSR-7 is available
        if (!class_exists(NyholmPsr17Factory::class)) {
            $this->markTestSkipped('Nyholm PSR-7 not available');
        }

        $client = new MarkupAiClient('test-token');
        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryMethodExists(): void
    {
        // Test that the auto-discovery method exists and is callable
        $client = new MarkupAiClient('test-token');
        $reflection = new \ReflectionClass($client);

        $this->assertTrue($reflection->hasMethod('autoDiscoverHttpDependencies'));

        $method = $reflection->getMethod('autoDiscoverHttpDependencies');
        $this->assertTrue($method->isPrivate());
    }

    public function testAutoDiscoveryWithPartialHttpClient(): void
    {
        // Test auto-discovery when only HTTP client is provided
        $httpClient = $this->createMock(ClientInterface::class);

        $client = new MarkupAiClient(
            token: 'test-token',
            httpClient: $httpClient
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryWithPartialRequestFactory(): void
    {
        // Test auto-discovery when only request factory is provided
        $requestFactory = $this->createMock(RequestFactoryInterface::class);

        $client = new MarkupAiClient(
            token: 'test-token',
            requestFactory: $requestFactory
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryWithPartialStreamFactory(): void
    {
        // Test auto-discovery when only stream factory is provided
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $client = new MarkupAiClient(
            token: 'test-token',
            streamFactory: $streamFactory
        );

        $this->assertInstanceOf(MarkupAiClient::class, $client);
    }

    public function testAutoDiscoveryInternalPathsCovered(): void
    {
        // Test to cover the alternative auto-discovery paths
        $client = new MarkupAiClient('test-token');
        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('autoDiscoverHttpDependencies');
        $method->setAccessible(true);

        // Test with partial discovery to cover alternative paths
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = null;
        $streamFactory = null;

        $method->invokeArgs($client, [&$httpClient, &$requestFactory, &$streamFactory]);

        $this->assertNotNull($requestFactory);
        $this->assertNotNull($streamFactory);
    }
}