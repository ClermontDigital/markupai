# MarkupAI PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/markupai/php-sdk.svg?style=flat-square)](https://packagist.org/packages/markupai/php-sdk)

A PHP SDK for integrating with the [Markup.ai](https://markup.ai) content governance platform. This library provides a clean, PSR-compliant interface for accessing Markup.ai's style checking, content validation, and automated rewriting capabilities.

## Installation

Install the package via Composer:

```bash
composer require markupai/php-sdk
```

### Laravel Integration

When used inside a Laravel 9+ application the package is auto-discovered. Configure your API token by setting `MARKUPAI_API_TOKEN` in the environment (or editing `config/markupai.php` after publishing).

```bash
php artisan vendor:publish --tag=markupai-config
```

Access the client through dependency injection or the optional facade:

```php
use MarkupAI\MarkupAiClient;
use MarkupAI\Laravel\Facades\MarkupAi;

// Constructor injection
public function __construct(MarkupAiClient $client)
{
    $this->client = $client;
}

// Facade usage
$styleGuides = MarkupAi::styleGuides()->list();
```

## Requirements

- PHP 8.1 or higher
- PSR-18 HTTP client implementation (e.g., Guzzle)
- PSR-7 HTTP message implementation

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use MarkupAI\MarkupAiClient;

// Initialize the client
$client = new MarkupAiClient('your-api-token');

// List all style guides
$styleGuides = $client->styleGuides()->list();

// Create a style check
$styleCheck = $client->styleChecks()->create([
    'content' => 'Your content to check',
    'style_guide_id' => 'your-style-guide-id'
]);

// Get style suggestions
$suggestions = $client->styleSuggestions()->create([
    'content' => 'Your content to improve',
    'style_guide_id' => 'your-style-guide-id'
]);

// Generate a style rewrite
$rewrite = $client->styleRewrites()->create([
    'content' => 'Your content to rewrite',
    'style_guide_id' => 'your-style-guide-id'
]);
```

## API Reference

### Style Guides

```php
// List all style guides
$styleGuides = $client->styleGuides()->list();

// Create a new style guide
$styleGuide = $client->styleGuides()->create([
    'name' => 'My Style Guide',
    'description' => 'A custom style guide'
]);

// Get a specific style guide
$styleGuide = $client->styleGuides()->get('style-guide-id');

// Update a style guide
$styleGuide = $client->styleGuides()->update('style-guide-id', [
    'name' => 'Updated Style Guide'
]);

// Delete a style guide
$client->styleGuides()->delete('style-guide-id');
```

### Style Checks

```php
// Create a style check
$styleCheck = $client->styleChecks()->create([
    'content' => 'Content to validate',
    'style_guide_id' => 'your-style-guide-id'
]);

// Get style check results
$styleCheck = $client->styleChecks()->get('style-check-id');

// Check if completed
if ($styleCheck->isCompleted()) {
    $results = $styleCheck->getResults();
}
```

### Style Suggestions

```php
// Create style suggestions
$suggestions = $client->styleSuggestions()->create([
    'content' => 'Content to improve',
    'style_guide_id' => 'your-style-guide-id'
]);

// Get suggestions
$suggestions = $client->styleSuggestions()->get('suggestion-id');

if ($suggestions->isCompleted()) {
    $suggestionData = $suggestions->getSuggestions();
}
```

### Style Rewrites

```php
// Create a style rewrite
$rewrite = $client->styleRewrites()->create([
    'content' => 'Content to rewrite',
    'style_guide_id' => 'your-style-guide-id'
]);

// Get rewritten content
$rewrite = $client->styleRewrites()->get('rewrite-id');

if ($rewrite->isCompleted()) {
    $rewrittenContent = $rewrite->getRewrittenContent();
}
```

## Configuration

### Custom HTTP Client

You can provide your own PSR-18 HTTP client:

```php
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;

$httpClient = new GuzzleClient();
$factory = new Psr17Factory();

$client = new MarkupAiClient(
    token: 'your-api-token',
    httpClient: $httpClient,
    requestFactory: $factory,
    streamFactory: $factory
);
```

### Custom Base URL

```php
$client = new MarkupAiClient(
    token: 'your-api-token',
    baseUrl: 'https://custom-api.markup.ai/v1'
);
```

## Error Handling

The SDK provides specific exception types for different error conditions:

```php
use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\ValidationException;
use MarkupAI\Exceptions\RateLimitException;
use MarkupAI\Exceptions\ServerException;

try {
    $styleGuides = $client->styleGuides()->list();
} catch (AuthenticationException $e) {
    // Handle authentication errors (401)
    echo 'Invalid API token: ' . $e->getMessage();
} catch (ValidationException $e) {
    // Handle validation errors (422)
    echo 'Validation error: ' . $e->getMessage();
} catch (RateLimitException $e) {
    // Handle rate limiting (429)
    echo 'Rate limit exceeded: ' . $e->getMessage();
} catch (ServerException $e) {
    // Handle server errors (500+)
    echo 'Server error: ' . $e->getMessage();
}
```

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer phpstan

# Fix code style
composer cs-fix

# Check code style
composer cs-check
```

### Requirements for Development

```bash
# Install development dependencies
composer install

# Install suggested packages for HTTP client
composer require guzzlehttp/guzzle nyholm/psr7
```

### Running Integration Tests

Integration tests require a valid Markup.ai API token:

```bash
# Copy the example environment file
cp .env.testing.example .env.testing

# Edit .env.testing and add your API token
# MARKUPAI_API_TOKEN=your_actual_token_here

# Run all tests (integration tests will be skipped if no token is provided)
composer test

# Run only unit tests (no API token required)
vendor/bin/phpunit tests/Unit/
```

**Security Note**: Never commit real API tokens to source control. Integration tests will automatically skip if no token is provided.

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE) file for details.

## Support

For support, please visit the [Markup.ai documentation](https://docs.markup.ai) or contact support@markup.ai.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
