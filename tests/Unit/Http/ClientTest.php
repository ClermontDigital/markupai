<?php

declare(strict_types=1);

namespace MarkupAI\Tests\Unit\Http;

use MarkupAI\Configuration;
use MarkupAI\Exceptions\AuthenticationException;
use MarkupAI\Exceptions\MarkupAiException;
use MarkupAI\Exceptions\NotFoundException;
use MarkupAI\Exceptions\RateLimitException;
use MarkupAI\Exceptions\ServerException;
use MarkupAI\Exceptions\ValidationException;
use MarkupAI\Http\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class ClientTest extends TestCase
{
    private Configuration $config;

    private ClientInterface $httpClient;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    private Client $client;

    protected function setUp(): void
    {
        $this->config = new Configuration('test-token');
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);

        $this->client = new Client(
            $this->config,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );
    }

    public function testGetRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://api.markup.ai/v1/test-path?param=value')
            ->willReturn($request);

        $request
            ->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"success": true}');

        $result = $this->client->get('test-path', ['param' => 'value']);

        $this->assertEquals(['success' => true], $result);
    }

    public function testPostRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://api.markup.ai/v1/test-path')
            ->willReturn($request);

        $request
            ->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $this->streamFactory
            ->expects($this->once())
            ->method('createStream')
            ->with('{"data":"test"}')
            ->willReturn($bodyStream);

        $request
            ->expects($this->once())
            ->method('withBody')
            ->with($bodyStream)
            ->willReturnSelf();

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"id": "123"}');

        $result = $this->client->post('test-path', ['data' => 'test']);

        $this->assertEquals(['id' => '123'], $result);
    }

    public function testPatchRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('PATCH', 'https://api.markup.ai/v1/test-path')
            ->willReturn($request);

        $request
            ->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $this->streamFactory
            ->expects($this->once())
            ->method('createStream')
            ->with('{"data":"updated"}')
            ->willReturn($bodyStream);

        $request
            ->expects($this->once())
            ->method('withBody')
            ->with($bodyStream)
            ->willReturnSelf();

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"updated": true}');

        $result = $this->client->patch('test-path', ['data' => 'updated']);

        $this->assertEquals(['updated' => true], $result);
    }

    public function testDeleteRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('DELETE', 'https://api.markup.ai/v1/test-path')
            ->willReturn($request);

        $request
            ->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('');

        $result = $this->client->delete('test-path');

        $this->assertEquals([], $result);
    }

    public function testAuthenticationError(): void
    {
        $this->expectException(AuthenticationException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(401);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Unauthorized"}');

        $this->client->get('test-path');
    }

    public function testNotFoundError(): void
    {
        $this->expectException(NotFoundException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Not Found"}');

        $this->client->get('test-path');
    }

    public function testValidationError(): void
    {
        $this->expectException(ValidationException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(422);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Validation failed"}');

        $this->client->get('test-path');
    }

    public function testRateLimitError(): void
    {
        $this->expectException(RateLimitException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(429);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Rate limit exceeded"}');

        $this->client->get('test-path');
    }

    public function testServerError(): void
    {
        $this->expectException(ServerException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Internal Server Error"}');

        $this->client->get('test-path');
    }

    public function testGenericError(): void
    {
        $this->expectException(MarkupAiException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Bad Request"}');

        $this->client->get('test-path');
    }

    public function testInvalidJsonResponse(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('invalid json');

        $this->client->get('test-path');
    }

    public function testEmptyBodyHandling(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('');

        $result = $this->client->get('test-path');

        $this->assertEquals([], $result);
    }

    public function testHttpClientException(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('HTTP request failed');

        $request = $this->createMock(RequestInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient
            ->method('sendRequest')
            ->willThrowException(new \Exception('Connection failed'));

        $this->client->get('test-path');
    }

    public function testPostWithEmptyData(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://api.markup.ai/v1/test-path')
            ->willReturn($request);

        $request
            ->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        // Should not call createStream or withBody for empty data
        $this->streamFactory
            ->expects($this->never())
            ->method('createStream');

        $request
            ->expects($this->never())
            ->method('withBody');

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"success": true}');

        $result = $this->client->post('test-path', []);

        $this->assertEquals(['success' => true], $result);
    }

    public function testErrorWithInvalidJsonBody(): void
    {
        $this->expectException(ValidationException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(422);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('invalid json response');

        $this->client->get('test-path');
    }

    public function testErrorWithMessageField(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Custom error message');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"message":"Custom error message"}');

        $this->client->get('test-path');
    }

    public function testErrorWithErrorField(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Error field message');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"error":"Error field message"}');

        $this->client->get('test-path');
    }

    public function testBuildUrlWithQueryParams(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://api.markup.ai/v1/path?param1=value1&param2=value2')
            ->willReturn($request);

        $request->method('withHeader')->willReturnSelf();
        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{}');

        $this->client->get('path', ['param1' => 'value1', 'param2' => 'value2']);
    }

    public function testBuildUrlWithLeadingSlash(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://api.markup.ai/v1/path')
            ->willReturn($request);

        $request->method('withHeader')->willReturnSelf();
        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{}');

        $this->client->get('/path');
    }

    public function testPatchWithEmptyData(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"success": true}');

        $result = $this->client->patch('test-path', []);

        $this->assertEquals(['success' => true], $result);
    }

    public function testErrorWithDetailField(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Detail field message');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('{"detail":"Detail field message"}');

        $this->client->get('test-path');
    }

    public function testSuccessfulResponseWithEmptyBody(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(204);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('');

        $result = $this->client->get('test-path');

        $this->assertEquals([], $result);
    }

    public function testErrorResponseWithNonJsonBody(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Unknown error');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('Plain text error message');

        $this->client->get('test-path');
    }

    public function testErrorResponseWithNullJsonData(): void
    {
        $this->expectException(MarkupAiException::class);
        $this->expectExceptionMessage('Unknown error');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();

        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn('null');

        $this->client->get('test-path');
    }
}