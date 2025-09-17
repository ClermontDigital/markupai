<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Integration;

use MarkupAI\MarkupAiClient;
use MarkupAI\Models\StyleGuide;
use MarkupAI\Models\StyleCheck;
use MarkupAI\Models\StyleSuggestion;
use MarkupAI\Models\StyleRewrite;
use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class MarkupAiIntegrationTest extends TestCase
{
    private MarkupAiClient $client;

    private static ?string $createdStyleGuideId = null;

    protected function setUp(): void
    {
        $apiToken = $this->getApiToken();
        if (empty($apiToken)) {
            $this->markTestSkipped('MARKUPAI_API_TOKEN environment variable not set');
        }

        $this->client = new MarkupAiClient($apiToken);
    }

    private function getApiToken(): string
    {
        // Try multiple sources for the API token
        return $_ENV['MARKUPAI_API_TOKEN'] ??
               getenv('MARKUPAI_API_TOKEN') ?:
               'mat_r3KWmbzVbVPW50GKQMa9GppNrrnf';
    }

    public function testAuthenticationWithValidToken(): void
    {
        $styleGuides = $this->client->styleGuides()->list();
        $this->assertIsArray($styleGuides);
    }

    public function testAuthenticationWithInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);

        $invalidClient = new MarkupAiClient('invalid-token');
        $invalidClient->styleGuides()->list();
    }

    public function testListStyleGuides(): void
    {
        $styleGuides = $this->client->styleGuides()->list();

        $this->assertIsArray($styleGuides);
        foreach ($styleGuides as $guide) {
            $this->assertInstanceOf(StyleGuide::class, $guide);
            $this->assertNotEmpty($guide->getId());
            $this->assertNotEmpty($guide->getName());
        }
    }

    public function testCreateStyleGuide(): void
    {
        $styleguideData = [
            'name' => 'Test Style Guide ' . time(),
            'description' => 'A test style guide created by PHP SDK integration tests',
        ];

        $styleGuide = $this->client->styleGuides()->create($styleguideData);

        $this->assertInstanceOf(StyleGuide::class, $styleGuide);
        $this->assertEquals($styleguideData['name'], $styleGuide->getName());
        $this->assertNotEmpty($styleGuide->getId());

        // Store for cleanup and other tests
        self::$createdStyleGuideId = $styleGuide->getId();
    }

    public function testGetStyleGuide(): void
    {
        // First ensure we have a style guide
        if (self::$createdStyleGuideId === null) {
            $this->testCreateStyleGuide();
        }

        $styleGuide = $this->client->styleGuides()->get(self::$createdStyleGuideId ?? '');

        $this->assertInstanceOf(StyleGuide::class, $styleGuide);
        $this->assertEquals(self::$createdStyleGuideId, $styleGuide->getId());
    }

    public function testUpdateStyleGuide(): void
    {
        // First ensure we have a style guide
        if (self::$createdStyleGuideId === null) {
            $this->testCreateStyleGuide();
        }

        $updateData = [
            'name' => 'Updated Test Style Guide ' . time(),
        ];

        $styleGuide = $this->client->styleGuides()->update(self::$createdStyleGuideId ?? '', $updateData);

        $this->assertInstanceOf(StyleGuide::class, $styleGuide);
        $this->assertEquals($updateData['name'], $styleGuide->getName());
    }

    public function testCreateStyleCheck(): void
    {
        // Get available style guides
        $styleGuides = $this->client->styleGuides()->list();
        if (empty($styleGuides)) {
            $this->testCreateStyleGuide();
            $styleGuideId = self::$createdStyleGuideId;
        } else {
            $styleGuideId = $styleGuides[0]->getId();
        }

        $checkData = [
            'content' => 'This is test content that needs to be checked for style compliance. It might have some issues with grammar or style.',
            'style_guide_id' => $styleGuideId,
        ];

        $styleCheck = $this->client->styleChecks()->create($checkData);

        $this->assertInstanceOf(StyleCheck::class, $styleCheck);
        $this->assertNotEmpty($styleCheck->getId());
        $this->assertContains($styleCheck->getStatus(), ['running', 'completed', 'failed']);

        // Test getting the style check
        $retrievedCheck = $this->client->styleChecks()->get($styleCheck->getId());
        $this->assertInstanceOf(StyleCheck::class, $retrievedCheck);
        $this->assertEquals($styleCheck->getId(), $retrievedCheck->getId());
    }

    public function testCreateStyleSuggestion(): void
    {
        // Get available style guides
        $styleGuides = $this->client->styleGuides()->list();
        if (empty($styleGuides)) {
            $this->testCreateStyleGuide();
            $styleGuideId = self::$createdStyleGuideId;
        } else {
            $styleGuideId = $styleGuides[0]->getId();
        }

        $suggestionData = [
            'content' => 'This content could be improved with better style and clarity. It has some redundant phrases and could be more concise.',
            'style_guide_id' => $styleGuideId,
        ];

        $styleSuggestion = $this->client->styleSuggestions()->create($suggestionData);

        $this->assertInstanceOf(StyleSuggestion::class, $styleSuggestion);
        $this->assertNotEmpty($styleSuggestion->getId());
        $this->assertContains($styleSuggestion->getStatus(), ['running', 'completed', 'failed']);

        // Test getting the style suggestion
        $retrievedSuggestion = $this->client->styleSuggestions()->get($styleSuggestion->getId());
        $this->assertInstanceOf(StyleSuggestion::class, $retrievedSuggestion);
        $this->assertEquals($styleSuggestion->getId(), $retrievedSuggestion->getId());
    }

    public function testCreateStyleRewrite(): void
    {
        // Get available style guides
        $styleGuides = $this->client->styleGuides()->list();
        if (empty($styleGuides)) {
            $this->testCreateStyleGuide();
            $styleGuideId = self::$createdStyleGuideId;
        } else {
            $styleGuideId = $styleGuides[0]->getId();
        }

        $rewriteData = [
            'content' => 'This text needs to be rewritten according to our style guidelines. It should be more professional and concise.',
            'style_guide_id' => $styleGuideId,
            'dialect' => 'american_english',
            'tone' => 'professional',
        ];

        $styleRewrite = $this->client->styleRewrites()->create($rewriteData);

        $this->assertInstanceOf(StyleRewrite::class, $styleRewrite);
        $this->assertNotEmpty($styleRewrite->getId());
        $this->assertContains($styleRewrite->getStatus(), ['running', 'completed', 'failed']);

        // Test getting the style rewrite
        $retrievedRewrite = $this->client->styleRewrites()->get($styleRewrite->getId());
        $this->assertInstanceOf(StyleRewrite::class, $retrievedRewrite);
        $this->assertEquals($styleRewrite->getId(), $retrievedRewrite->getId());
    }

    public function testValidationError(): void
    {
        $this->expectException(ValidationException::class);

        // Try to create a style guide without required fields
        $this->client->styleGuides()->create([]);
    }

    public function testNotFoundError(): void
    {
        $this->expectException(\MarkupAI\Exceptions\MarkupAiException::class);

        // Try to get a non-existent style guide
        $this->client->styleGuides()->get('non-existent-id');
    }

    protected function tearDown(): void
    {
        // Clean up created style guide
        if (self::$createdStyleGuideId !== null) {
            try {
                $this->client->styleGuides()->delete(self::$createdStyleGuideId);
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
            self::$createdStyleGuideId = null;
        }
    }
}
