<?php
namespace Ratchet\Http;
use OverflowException;
use Ratchet\Http\HttpRequestParser;

/**
 * @covers Ratchet\Http\HttpRequestParser
 */
class HttpRequestParserTest extends \PHPUnit\Framework\TestCase {
    protected $parser;

    public function setUp():void {
        $this->parser = new HttpRequestParser;
    }

    public function headersProvider() {
        return array(
            array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n")
        );
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, $message) {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testBufferOverflowResponse() {
        $conn = $this->createMock(\Ratchet\ConnectionInterface::class);

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onMessage($conn, "GET / HTTP/1.1\r\n"));

        $this->expectException(OverflowException::class);

        $this->parser->onMessage($conn, "Header-Is: Too Big");
    }

    public function testReturnTypeIsRequest() {
        $conn = $this->createMock(\Ratchet\ConnectionInterface::class);
        $return = $this->parser->onMessage($conn, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n");

        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $return);
    }
}
