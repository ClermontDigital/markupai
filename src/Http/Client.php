<?php

declare(strict_types=1);

namespace MarkupAI\Http;

use MarkupAI\Configuration;
use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\MarkupAiException;
use MarkupAI\Exceptions\NotFoundException;
use MarkupAI\Exceptions\RateLimitException;
use MarkupAI\Exceptions\ServerException;
use MarkupAI\Exceptions\ValidationException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private Configuration $config;

    public function __construct(
        Configuration $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function get(string $path, array $queryParams = []): array
    {
        $url = $this->buildUrl($path, $queryParams);
        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $this->addDefaultHeaders($request);

        return $this->sendRequest($request);
    }

    public function post(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $request = $this->requestFactory->createRequest('POST', $url);
        $request = $this->addDefaultHeaders($request);
        $request = $this->addJsonBody($request, $data);

        return $this->sendRequest($request);
    }

    public function postWithFile(string $path, array $data = [], ?string $filePath = null, array $allowedExtensions = []): array
    {
        if ($filePath !== null && !empty($allowedExtensions)) {
            $this->validateFileType($filePath, $allowedExtensions);
        }

        $url = $this->buildUrl($path);
        $request = $this->requestFactory->createRequest('POST', $url);
        $request = $this->addFileUploadHeaders($request);
        $request = $this->addMultipartBody($request, $data, $filePath);

        return $this->sendRequest($request);
    }

    public function patch(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $request = $this->requestFactory->createRequest('PATCH', $url);
        $request = $this->addDefaultHeaders($request);
        $request = $this->addJsonBody($request, $data);

        return $this->sendRequest($request);
    }

    public function delete(string $path): array
    {
        $url = $this->buildUrl($path);
        $request = $this->requestFactory->createRequest('DELETE', $url);
        $request = $this->addDefaultHeaders($request);

        return $this->sendRequest($request);
    }

    private function buildUrl(string $path, array $queryParams = []): string
    {
        $url = $this->config->getBaseUrl() . '/' . ltrim($path, '/');

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    private function addDefaultHeaders(RequestInterface $request): RequestInterface
    {
        return $request
            ->withHeader('Authorization', $this->config->getAuthorizationHeader())
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'MarkupAI-PHP-SDK/1.0.2');
    }

    private function addFileUploadHeaders(RequestInterface $request): RequestInterface
    {
        return $request
            ->withHeader('Authorization', $this->config->getAuthorizationHeader())
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'MarkupAI-PHP-SDK/1.0.2');
    }

    private function addJsonBody(RequestInterface $request, array $data): RequestInterface
    {
        if (empty($data)) {
            return $request;
        }

        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $stream = $this->streamFactory->createStream($json);

        return $request->withBody($stream);
    }

    private function addMultipartBody(RequestInterface $request, array $data, ?string $filePath): RequestInterface
    {
        $boundary = uniqid('boundary_', true);
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);

        $multipartData = '';

        // Add regular form fields
        foreach ($data as $key => $value) {
            $multipartData .= "--{$boundary}\r\n";
            $multipartData .= "Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n";

            // Handle arrays by JSON encoding them
            if (is_array($value)) {
                $multipartData .= json_encode($value) . "\r\n";
            } else {
                $multipartData .= "{$value}\r\n";
            }
        }

        // Add file upload if provided
        if ($filePath !== null && file_exists($filePath)) {
            $filename = basename($filePath);
            $fileContent = file_get_contents($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

            $multipartData .= "--{$boundary}\r\n";
            $multipartData .= "Content-Disposition: form-data; name=\"file_upload\"; filename=\"{$filename}\"\r\n";
            $multipartData .= "Content-Type: {$mimeType}\r\n\r\n";
            $multipartData .= "{$fileContent}\r\n";
        }

        $multipartData .= "--{$boundary}--\r\n";

        $stream = $this->streamFactory->createStream($multipartData);
        return $request->withBody($stream);
    }

    private function sendRequest(RequestInterface $request): array
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            throw new MarkupAiException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->handleResponse($response);
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($statusCode >= 200 && $statusCode < 300) {
            return $this->parseJsonResponse($body);
        }

        $this->handleErrorResponse($statusCode, $body);
    }

    private function parseJsonResponse(string $body): array
    {
        if (empty($body)) {
            return [];
        }

        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new MarkupAiException('Invalid JSON response: ' . $e->getMessage(), 0, $e);
        }
    }

    private function handleErrorResponse(int $statusCode, string $body): never
    {
        $errorData = [];

        try {
            $errorData = json_decode($body, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException $e) {
            // Ignore JSON parsing errors for error responses
        }

        $message = $errorData['message'] ?? $errorData['error'] ?? $errorData['detail'] ?? 'Unknown error';
        $context = ['status_code' => $statusCode, 'response_body' => $body];

        match (true) {
            $statusCode === 401 => throw new AuthenticationException($message, $statusCode, null, $context),
            $statusCode === 404 => throw new NotFoundException($message, $statusCode, null, $context),
            $statusCode === 422 => throw new ValidationException($message, $statusCode, null, $context),
            $statusCode === 429 => throw new RateLimitException($message, $statusCode, null, $context),
            $statusCode >= 500 => throw new ServerException($message, $statusCode, null, $context),
            default => throw new MarkupAiException($message, $statusCode, null, $context),
        };
    }

    private function validateFileType(string $filePath, array $allowedExtensions): void
    {
        if (!file_exists($filePath)) {
            throw new ValidationException("File not found: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $normalizedAllowed = array_map('strtolower', $allowedExtensions);

        // Allow files without extensions (temp files) or with valid extensions
        if (!empty($extension) && !in_array($extension, $normalizedAllowed, true)) {
            throw new ValidationException(
                sprintf(
                    'Invalid file type "%s". Allowed types: %s',
                    $extension,
                    implode(', ', $allowedExtensions)
                )
            );
        }

        // Check file size (15MB max as per docs)
        $fileSize = filesize($filePath);
        $maxSize = 15 * 1024 * 1024; // 15MB in bytes

        if ($fileSize > $maxSize) {
            throw new ValidationException(
                sprintf(
                    'File size %s exceeds maximum allowed size of 15MB',
                    $this->formatBytes($fileSize)
                )
            );
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
