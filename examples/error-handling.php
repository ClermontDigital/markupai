<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MarkupAI\MarkupAiClient;
use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\ValidationException;
use MarkupAI\Exceptions\RateLimitException;
use MarkupAI\Exceptions\ServerException;
use MarkupAI\Exceptions\MarkupAiException;

echo "=== MarkupAI PHP SDK Error Handling Example ===\n\n";

// Example 1: Authentication Error
echo "1. Testing authentication error:\n";
try {
    $client = new MarkupAiClient('invalid-token');
    $styleGuides = $client->styleGuides()->list();
} catch (AuthenticationException $e) {
    echo "   ✓ Caught AuthenticationException: {$e->getMessage()}\n";
    echo "   Status Code: {$e->getCode()}\n";
} catch (MarkupAiException $e) {
    echo "   Caught general MarkupAiException: {$e->getMessage()}\n";
}
echo "\n";

// Example 2: Validation Error
echo "2. Testing validation error:\n";
try {
    $client = new MarkupAiClient('your-api-token-here');

    // This should cause a validation error due to missing required fields
    $styleGuide = $client->styleGuides()->create([]);
} catch (ValidationException $e) {
    echo "   ✓ Caught ValidationException: {$e->getMessage()}\n";
    echo "   Status Code: {$e->getCode()}\n";

    $context = $e->getContext();
    if (isset($context['response_body'])) {
        echo "   Response Body: {$context['response_body']}\n";
    }
} catch (MarkupAiException $e) {
    echo "   Caught general MarkupAiException: {$e->getMessage()}\n";
}
echo "\n";

// Example 3: Rate Limiting
echo "3. Simulating rate limit handling:\n";
echo "   (This would normally occur after many rapid requests)\n";
try {
    // In a real scenario, this would happen after exceeding rate limits
    throw new RateLimitException('Rate limit exceeded. Please try again later.', 429);
} catch (RateLimitException $e) {
    echo "   ✓ Caught RateLimitException: {$e->getMessage()}\n";
    echo "   Recommended action: Implement exponential backoff\n";
}
echo "\n";

// Example 4: Server Error
echo "4. Simulating server error handling:\n";
try {
    throw new ServerException('Internal server error occurred', 500);
} catch (ServerException $e) {
    echo "   ✓ Caught ServerException: {$e->getMessage()}\n";
    echo "   Recommended action: Retry with exponential backoff\n";
}
echo "\n";

// Example 5: Comprehensive Error Handling
echo "5. Comprehensive error handling pattern:\n";

function handleMarkupAiRequest(callable $request): void
{
    $maxRetries = 3;
    $retryDelay = 1; // seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $result = $request();
            echo "   ✓ Request successful!\n";
            return;
        } catch (AuthenticationException $e) {
            echo "   ✗ Authentication failed: {$e->getMessage()}\n";
            echo "   Action: Check your API token\n";
            break; // Don't retry authentication errors
        } catch (ValidationException $e) {
            echo "   ✗ Validation error: {$e->getMessage()}\n";
            echo "   Action: Check your request data\n";
            break; // Don't retry validation errors
        } catch (RateLimitException $e) {
            echo "   ⚠ Rate limit exceeded (attempt {$attempt}/{$maxRetries})\n";
            if ($attempt < $maxRetries) {
                $delay = $retryDelay * pow(2, $attempt - 1); // Exponential backoff
                echo "   Waiting {$delay} seconds before retry...\n";
                sleep($delay);
            }
        } catch (ServerException $e) {
            echo "   ⚠ Server error (attempt {$attempt}/{$maxRetries}): {$e->getMessage()}\n";
            if ($attempt < $maxRetries) {
                $delay = $retryDelay * $attempt;
                echo "   Waiting {$delay} seconds before retry...\n";
                sleep($delay);
            }
        } catch (MarkupAiException $e) {
            echo "   ✗ Unexpected MarkupAI error: {$e->getMessage()}\n";
            echo "   Error code: {$e->getCode()}\n";
            break;
        } catch (Exception $e) {
            echo "   ✗ General error: {$e->getMessage()}\n";
            break;
        }
    }

    echo "   ✗ Request failed after {$maxRetries} attempts\n";
}

// Simulate a request that might fail
handleMarkupAiRequest(function () {
    // This would be your actual API call
    // For demo purposes, we'll simulate different types of errors
    $random = rand(1, 4);

    switch ($random) {
        case 1:
            throw new RateLimitException('Rate limit exceeded', 429);
        case 2:
            throw new ServerException('Internal server error', 500);
        case 3:
            throw new ValidationException('Invalid request data', 422);
        default:
            return ['success' => true]; // Success case
    }
});

echo "\n=== Error Handling Example completed ===\n";