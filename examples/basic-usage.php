<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MarkupAI\MarkupAiClient;
use MarkupAI\Exceptions\MarkupAiException;

// Initialize the client with your API token
$client = new MarkupAiClient('your-api-token-here');

try {
    echo "=== MarkupAI PHP SDK Basic Usage Example ===\n\n";

    // List all available style guides
    echo "1. Listing all style guides:\n";
    $styleGuides = $client->styleGuides()->list();

    foreach ($styleGuides as $guide) {
        echo "   - {$guide->getName()} (ID: {$guide->getId()})\n";
    }
    echo "\n";

    // If you have style guides, use the first one for the example
    if (!empty($styleGuides)) {
        $styleGuideId = $styleGuides[0]->getId();
        $content = "This is some sample content that we want to check against our style guide. It might have grammar issues or style problems.";

        // Create a style check
        echo "2. Creating a style check:\n";
        $styleCheck = $client->styleChecks()->create([
            'content' => $content,
            'style_guide_id' => $styleGuideId,
        ]);

        echo "   Style check created with ID: {$styleCheck->getId()}\n";
        echo "   Status: {$styleCheck->getStatus()}\n\n";

        // Poll for completion (in a real application, you might use webhooks)
        echo "3. Waiting for style check to complete...\n";
        $maxAttempts = 10;
        $attempt = 0;

        while (!$styleCheck->isCompleted() && $attempt < $maxAttempts) {
            sleep(2);
            $styleCheck = $client->styleChecks()->get($styleCheck->getId());
            $attempt++;
            echo "   Attempt {$attempt}: Status is {$styleCheck->getStatus()}\n";
        }

        if ($styleCheck->isCompleted()) {
            echo "   ✓ Style check completed!\n";
            if ($styleCheck->getResults()) {
                echo "   Results: " . json_encode($styleCheck->getResults(), JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "   ⚠ Style check did not complete within the timeout period\n";
        }
        echo "\n";

        // Create style suggestions
        echo "4. Creating style suggestions:\n";
        $suggestions = $client->styleSuggestions()->create([
            'content' => $content,
            'style_guide_id' => $styleGuideId,
        ]);

        echo "   Style suggestions created with ID: {$suggestions->getId()}\n";
        echo "   Status: {$suggestions->getStatus()}\n\n";

        // Create a style rewrite
        echo "5. Creating a style rewrite:\n";
        $rewrite = $client->styleRewrites()->create([
            'content' => $content,
            'style_guide_id' => $styleGuideId,
        ]);

        echo "   Style rewrite created with ID: {$rewrite->getId()}\n";
        echo "   Status: {$rewrite->getStatus()}\n";

        // Poll for rewrite completion
        $attempt = 0;
        while (!$rewrite->isCompleted() && $attempt < $maxAttempts) {
            sleep(2);
            $rewrite = $client->styleRewrites()->get($rewrite->getId());
            $attempt++;
        }

        if ($rewrite->isCompleted() && $rewrite->getRewrittenContent()) {
            echo "   ✓ Rewrite completed!\n";
            echo "   Original: {$content}\n";
            echo "   Rewritten: {$rewrite->getRewrittenContent()}\n";
        }
    } else {
        echo "No style guides found. Please create a style guide first.\n";
    }

    echo "\n=== Example completed successfully! ===\n";

} catch (MarkupAiException $e) {
    echo "MarkupAI Error: {$e->getMessage()}\n";
    echo "Error code: {$e->getCode()}\n";

    $context = $e->getContext();
    if (!empty($context)) {
        echo "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "General Error: {$e->getMessage()}\n";
}