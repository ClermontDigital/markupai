<?php

declare(strict_types=1);

namespace MarkupAI\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use MarkupAI\MarkupAiClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MarkupAiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'markupai');

        $this->app->singleton(MarkupAiClient::class, function (Container $app) {
            $config = $app['config']->get('markupai', []);

            $token = $config['api_token'] ?? null;
            if (empty($token)) {
                throw new InvalidArgumentException(
                    'MarkupAI API token is not configured. Set MARKUPAI_API_TOKEN or config markupai.api_token.'
                );
            }

            $baseUrl = $config['base_url'] ?? 'https://api.markup.ai/v1';
            $timeout = (int) ($config['timeout'] ?? 30);

            $httpClient = $this->resolveDependency($app, $config['http_client'] ?? null, ClientInterface::class);
            $requestFactory = $this->resolveDependency($app, $config['request_factory'] ?? null, RequestFactoryInterface::class);
            $streamFactory = $this->resolveDependency($app, $config['stream_factory'] ?? null, StreamFactoryInterface::class);

            return new MarkupAiClient(
                token: $token,
                httpClient: $httpClient,
                requestFactory: $requestFactory,
                streamFactory: $streamFactory,
                baseUrl: $baseUrl,
                timeout: $timeout
            );
        });

        $this->app->alias(MarkupAiClient::class, 'markupai');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => config_path('markupai.php'),
            ], 'markupai-config');
        }
    }

    private function configPath(): string
    {
        return __DIR__ . '/../../config/markupai.php';
    }

    private function resolveDependency(Container $app, mixed $value, string $expectedInterface): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = $app->make($value);
        } elseif (is_callable($value)) {
            $value = $value($app);
        }

        if (!$value instanceof $expectedInterface) {
            $message = sprintf('Configured dependency must implement %s.', $expectedInterface);
            throw new InvalidArgumentException($message);
        }

        return $value;
    }
}
