<?php

use HocVT\TikaSimple\TikaSimpleClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

final class TikaSimpleClientTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        // Tạo một mock client GuzzleHttp
        $this->client = $this->createMock(Client::class);
    }

    public function testVersion()
    {
        // Thiết lập phản hồi giả cho yêu cầu phiên bản
        $this->client->method('request')
            ->with('GET', '/version', [])
            ->willReturn(new Response(200, [], '1.26.0'));

        $tikaClient = new TikaSimpleClient('http://127.0.0.1:9998', ['client' => $this->client]);
        $version = $tikaClient->version();

        $this->assertEquals('1.26.0', $version);
    }

    public function testLanguage()
    {
        $string = "Hello, this is a test string.";
        $this->client->method('request')
            ->with('PUT', '/language/stream', ['body' => $string])
            ->willReturn(new Response(200, [], 'en'));

        $tikaClient = new TikaSimpleClient('http://127.0.0.1:9998', ['client' => $this->client]);
        $language = $tikaClient->language($string);

        $this->assertEquals('en', $language);
    }

    public function testMime()
    {
        $content = "This is some sample content.";
        $this->client->method('request')
            ->with('PUT', '/detect/stream', ['body' => $content])
            ->willReturn(new Response(200, [], 'text/plain'));

        $tikaClient = new TikaSimpleClient('http://127.0.0.1:9998', ['client' => $this->client]);
        $mime = $tikaClient->mime($content);

        $this->assertEquals('text/plain', $mime);
    }

    public function testRmeta()
    {
        $content = "This is a test document.";
        $this->client->method('request')
            ->with('PUT', '/rmeta', ['body' => $content])
            ->willReturn(new Response(200, [], json_encode(['X-TIKA:content' => 'Test content', 'X-TIKA:metadata' => 'Some metadata'])));

        $tikaClient = new TikaSimpleClient('http://127.0.0.1:9998', ['client' => $this->client]);
        $metadata = $tikaClient->rmeta($content);

        $this->assertEquals('Test content', $metadata['X-TIKA:content']);
    }

    public function testMimeFile()
    {
        $path = 'path/to/sample.txt';
        $fh = fopen($path, 'r+');
        $this->client->method('request')
            ->with('PUT', '/detect/stream', ['body' => $fh])
            ->willReturn(new Response(200, [], 'text/plain'));

        $tikaClient = new TikaSimpleClient('http://127.0.0.1:9998', ['client' => $this->client]);
        $mime = $tikaClient->mimeFile($path);

        $this->assertEquals('text/plain', $mime);
        fclose($fh); // Đảm bảo đóng tệp sau khi kiểm tra
    }

    // Thêm các phương thức kiểm tra khác nếu cần
}