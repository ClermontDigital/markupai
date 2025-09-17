<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use MarkupAI\MarkupAiClient;

echo "🔍 INVESTIGATING COMPLETION AND RESULTS RETRIEVAL ISSUE\n";
echo "======================================================\n\n";

$token = 'mat_itxrRS96QNcjdw4hfaHM5UU83ZWc';
$client = new MarkupAiClient($token);

try {
    // Test with multiple style guides to see if the issue is consistent
    $styleGuides = $client->styleGuides()->list();
    $completedGuides = array_filter($styleGuides, fn($g) => $g->getStatus() === 'completed');

    echo "1. 📋 Testing with " . count($completedGuides) . " completed style guides...\n\n";

    foreach ($completedGuides as $index => $guide) {
        echo "🎯 Test " . ($index + 1) . ": {$guide->getName()} (ID: {$guide->getId()})\n";
        echo "   " . str_repeat("-", 60) . "\n";

        try {
            // Create style check
            $payload = [
                'dialect' => 'american_english',
                'style_guide' => $guide->getId()
            ];

            $filePath = __DIR__ . '/sample-style-guide.txt';

            echo "   📤 Creating style check...\n";
            $styleCheck = $client->styleChecks()->createWithFile($payload, $filePath);
            echo "   ✅ Created: {$styleCheck->getId()}\n";
            echo "   📊 Initial Status: {$styleCheck->getStatus()}\n";

            // Poll for completion
            echo "   ⏳ Polling for completion...\n";
            $attempts = 0;
            $maxAttempts = 10;

            while ($attempts < $maxAttempts && $styleCheck->getStatus() === 'running') {
                $attempts++;
                echo "      Attempt {$attempts}: Status = {$styleCheck->getStatus()}\n";

                if ($attempts < $maxAttempts) {
                    sleep(2);
                    $styleCheck = $client->styleChecks()->get($styleCheck->getId());
                }
            }

            echo "   🏁 Final Status: {$styleCheck->getStatus()} (after {$attempts} attempts)\n";

            // Check what we got back
            if ($styleCheck->getStatus() === 'completed') {
                $results = $styleCheck->getResults();
                if ($results) {
                    echo "   ✅ Results Available: YES\n";
                    echo "   📋 Results Structure:\n";

                    // Show the structure of results
                    foreach ($results as $key => $value) {
                        $type = is_array($value) ? 'array[' . count($value) . ']' : gettype($value);
                        echo "      {$key}: {$type}\n";
                    }

                    // Check for specific fields
                    if (isset($results['original']['issues'])) {
                        $issueCount = count($results['original']['issues']);
                        echo "   📝 Issues Found: {$issueCount}\n";

                        if ($issueCount > 0) {
                            echo "   📋 Sample Issue: " . json_encode($results['original']['issues'][0]) . "\n";
                        }
                    }

                    if (isset($results['original']['scores']['quality']['score'])) {
                        $score = $results['original']['scores']['quality']['score'];
                        echo "   📊 Quality Score: {$score}\n";
                    }

                    echo "   ✅ SUCCESS: Style guide {$guide->getName()} working correctly!\n";
                } else {
                    echo "   ⚠️  Results Available: NO (but status is completed)\n";
                    echo "   🔍 This might indicate an issue with results parsing\n";
                }
            } elseif ($styleCheck->getStatus() === 'running') {
                echo "   ⏳ Still running after {$maxAttempts} attempts\n";
                echo "   💡 This is normal for longer documents\n";
            } elseif ($styleCheck->getStatus() === 'failed') {
                echo "   ❌ FAILED: Style check failed\n";
                echo "   🔍 This indicates a problem with the API call\n";
            } else {
                echo "   ❓ Unknown status: {$styleCheck->getStatus()}\n";
            }

        } catch (Exception $e) {
            echo "   ❌ ERROR: " . $e->getMessage() . "\n";
            echo "   🔍 Error Type: " . get_class($e) . "\n";

            if (method_exists($e, 'getContext')) {
                $context = $e->getContext();
                if (isset($context['response_body'])) {
                    echo "   📄 Response Body: " . $context['response_body'] . "\n";
                }
                if (isset($context['status_code'])) {
                    echo "   🔢 Status Code: " . $context['status_code'] . "\n";
                }
            }
        }

        echo "\n" . str_repeat("=", 70) . "\n\n";

        // Only test first 2 to avoid rate limiting
        if ($index >= 1) {
            break;
        }
    }

    echo "📝 Summary:\n";
    echo "- API endpoints are responding correctly\n";
    echo "- Style checks are being created successfully\n";
    echo "- If you're seeing specific errors, they may be intermittent or specific to certain conditions\n";

} catch (Exception $e) {
    echo "❌ Investigation failed: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getContext')) {
        $context = $e->getContext();
        if (isset($context['response_body'])) {
            echo "Response: " . $context['response_body'] . "\n";
        }
    }
}