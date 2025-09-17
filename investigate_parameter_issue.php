<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use MarkupAI\MarkupAiClient;

echo "🔍 INVESTIGATING STYLE GUIDE ID vs FILE ID ISSUE\n";
echo "================================================\n\n";

$token = 'mat_itxrRS96QNcjdw4hfaHM5UU83ZWc';
$client = new MarkupAiClient($token);

// First, let's examine what parameters we're actually sending
function debugApiCall($description, $endpoint, $data, $filePath = null) {
    echo "🔍 {$description}\n";
    echo "   Endpoint: {$endpoint}\n";
    echo "   Parameters: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    if ($filePath) {
        echo "   File: {$filePath}\n";
        echo "   File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n";
        if (file_exists($filePath)) {
            echo "   File size: " . filesize($filePath) . " bytes\n";
            echo "   File content preview: " . substr(file_get_contents($filePath), 0, 50) . "...\n";
        }
    }
    echo "\n";
}

try {
    // Get available style guides first
    echo "1. 📋 Getting available style guides...\n";
    $styleGuides = $client->styleGuides()->list();

    foreach ($styleGuides as $guide) {
        echo "   - {$guide->getName()} (ID: {$guide->getId()}, Status: {$guide->getStatus()})\n";
    }

    $testGuide = null;
    foreach ($styleGuides as $guide) {
        if ($guide->getStatus() === 'completed') {
            $testGuide = $guide;
            break;
        }
    }

    if (!$testGuide) {
        throw new Exception("No completed style guides available for testing");
    }

    echo "\n2. 🎯 Using style guide for testing: {$testGuide->getName()} (ID: {$testGuide->getId()})\n\n";

    // Test different parameter structures
    $testCases = [
        [
            'name' => 'Current Implementation',
            'params' => [
                'dialect' => 'american_english',
                'style_guide' => $testGuide->getId()  // This might be the issue
            ]
        ],
        [
            'name' => 'With style_guide_id',
            'params' => [
                'dialect' => 'american_english',
                'style_guide_id' => $testGuide->getId()  // Alternative parameter name
            ]
        ],
        [
            'name' => 'With nested style_guide object',
            'params' => [
                'dialect' => 'american_english',
                'style_guide' => [
                    'id' => $testGuide->getId()
                ]
            ]
        ],
        [
            'name' => 'Minimal parameters only',
            'params' => [
                'style_guide' => $testGuide->getId()
            ]
        ]
    ];

    $filePath = __DIR__ . '/sample-style-guide.txt';

    // Test each parameter structure
    foreach ($testCases as $index => $testCase) {
        echo "3.{$index} Testing: {$testCase['name']}\n";
        debugApiCall($testCase['name'], 'style/checks', $testCase['params'], $filePath);

        try {
            // Make direct curl call to see raw response
            $payload = $testCase['params'];
            $jsonPayload = json_encode($payload);

            echo "   Making direct API call...\n";
            $curlCommand = sprintf(
                'curl -X POST "https://api.markup.ai/v1/style/checks" ' .
                '-H "Authorization: Bearer %s" ' .
                '-H "Content-Type: multipart/form-data" ' .
                '-F "file_upload=@%s" %s',
                $token,
                $filePath,
                // Add form fields for each parameter
                implode(' ', array_map(
                    fn($k, $v) => sprintf('-F "%s=%s"', $k, is_array($v) ? json_encode($v) : $v),
                    array_keys($payload),
                    array_values($payload)
                ))
            );

            echo "   Raw curl command:\n   {$curlCommand}\n\n";

            $output = shell_exec($curlCommand . ' 2>&1');
            echo "   Raw API Response:\n";
            echo "   " . trim($output) . "\n\n";

            // Also test with our SDK
            try {
                $styleCheck = $client->styleChecks()->createWithFile($payload, $filePath);
                echo "   ✅ SDK Success: {$styleCheck->getId()} (Status: {$styleCheck->getStatus()})\n";
                break; // Stop on first success
            } catch (Exception $e) {
                echo "   ❌ SDK Error: " . $e->getMessage() . "\n";
                if (method_exists($e, 'getContext')) {
                    $context = $e->getContext();
                    if (isset($context['response_body'])) {
                        echo "   Response: " . $context['response_body'] . "\n";
                    }
                }
            }

        } catch (Exception $e) {
            echo "   ❌ Test failed: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n\n";
    }

} catch (Exception $e) {
    echo "❌ Investigation failed: " . $e->getMessage() . "\n";
}