<?php

declare(strict_types=1);

namespace MarkupAI;

use MarkupAI\Http\Client;
use MarkupAI\Resources\StyleChecks;
use MarkupAI\Resources\StyleGuides;
use MarkupAI\Resources\StyleRewrites;
use MarkupAI\Resources\StyleSuggestions;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory as GuzzleHttpHttpFactory;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Nyholm\Psr7\Factory\Psr17Factory as NyholmPsr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MarkupAiClient
{
    private Client $httpClient;

    private StyleGuides $styleGuides;

    private StyleChecks $styleChecks;

    private StyleSuggestions $styleSuggestions;

    private StyleRewrites $styleRewrites;

    public function __construct(
        string $token,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        string $baseUrl = 'https://api.markup.ai/v1',
        int $timeout = 30
    ) {
        $config = new Configuration($token, $baseUrl, $timeout);

        if ($httpClient === null || $requestFactory === null || $streamFactory === null) {
            $this->autoDiscoverHttpDependencies($httpClient, $requestFactory, $streamFactory);
        }

        $this->httpClient = new Client($config, $httpClient, $requestFactory, $streamFactory);

        $this->styleGuides = new StyleGuides($this->httpClient);
        $this->styleChecks = new StyleChecks($this->httpClient);
        $this->styleSuggestions = new StyleSuggestions($this->httpClient);
        $this->styleRewrites = new StyleRewrites($this->httpClient);
    }

    public function styleGuides(): StyleGuides
    {
        return $this->styleGuides;
    }

    public function styleChecks(): StyleChecks
    {
        return $this->styleChecks;
    }

    public function styleSuggestions(): StyleSuggestions
    {
        return $this->styleSuggestions;
    }

    public function styleRewrites(): StyleRewrites
    {
        return $this->styleRewrites;
    }

    private function autoDiscoverHttpDependencies(
        ?ClientInterface &$httpClient,
        ?RequestFactoryInterface &$requestFactory,
        ?StreamFactoryInterface &$streamFactory
    ): void {
        if ($httpClient === null) {
            if (class_exists(Psr18ClientDiscovery::class)) {
                $httpClient = Psr18ClientDiscovery::find();
            } elseif (class_exists(GuzzleHttpClient::class)) {
                $httpClient = new GuzzleHttpClient();
            }
        }

        if ($requestFactory === null) {
            if (class_exists(Psr17FactoryDiscovery::class)) {
                $requestFactory = Psr17FactoryDiscovery::findRequestFactory();
            } elseif (class_exists(NyholmPsr17Factory::class)) {
                $requestFactory = new NyholmPsr17Factory();
            } elseif (class_exists(GuzzleHttpHttpFactory::class)) {
                $requestFactory = new GuzzleHttpHttpFactory();
            }
        }

        if ($streamFactory === null) {
            if (class_exists(Psr17FactoryDiscovery::class)) {
                $streamFactory = Psr17FactoryDiscovery::findStreamFactory();
            } elseif (class_exists(NyholmPsr17Factory::class)) {
                $streamFactory = new NyholmPsr17Factory();
            } elseif (class_exists(GuzzleHttpHttpFactory::class)) {
                $streamFactory = new GuzzleHttpHttpFactory();
            }
        }

        if ($httpClient === null || $requestFactory === null || $streamFactory === null) {
            throw new \RuntimeException(
                'No PSR-18 HTTP client, PSR-17 request factory, or PSR-17 stream factory found. ' .
                'Please install guzzlehttp/guzzle and nyholm/psr7 or provide your own implementations.'
            );
        }
    }
}
