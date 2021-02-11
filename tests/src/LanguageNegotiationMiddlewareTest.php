<?php
namespace tests;

use Germania\LanguageNegotiation\LanguageNegotiationMiddleware;
use Negotiation\AbstractNegotiator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Psr\Http\Server\MiddlewareInterface;

class LanguageNegotiationMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

    public $acceptLanguageHeaderName = 'Accept-Language';
    public $acceptLanguageHeaderValue = 'en; q=0.1, fr; q=0.4, fu; q=0.9, de; q=0.2';

    public $request_attr_name = "X-language-negotiated";
    public $priorities = [ 'de', 'fu', 'en'];

    public $negotiator;


    public function setUp() : void
    {
        parent::setUp();
        $this->negotiator = new \Negotiation\LanguageNegotiator();
        $this->psr17Factory = new Psr17Factory;
    }

    public function testInstantiation() : LanguageNegotiationMiddleware
    {

        $sut = new LanguageNegotiationMiddleware($this->negotiator, $this->priorities);

        $this->assertIsCallable($sut);
        $this->assertInstanceOf(MiddlewareInterface::class, $sut);

        return $sut;
    }


    /**
     * @depends testInstantiation
     */
    public function testDoBusiness( LanguageNegotiationMiddleware $sut )
    {

        $result_request = $this->psr17Factory
                               ->createServerRequest("GET", "https://httpbin.org/")
                               ->withHeader($this->acceptLanguageHeaderName, $this->acceptLanguageHeaderValue);

        $result_request = $sut->doBusiness($result_request);
        $this->assertInstanceOf(ServerRequestInterface::class, $result_request);

        $attr_value = $result_request->getAttribute($this->request_attr_name);
        $this->assertIsString($attr_value);
        $this->assertNotEmpty($attr_value);
    }




    /**
     * @depends testInstantiation
     */
    public function testDoublePass( LanguageNegotiationMiddleware $sut )
    {

        $result_request = $this->psr17Factory
                               ->createServerRequest("GET", "https://httpbin.org/")
                               ->withHeader($this->acceptLanguageHeaderName, $this->acceptLanguageHeaderValue);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getHeaderLine(Argument::exact($this->acceptLanguageHeaderName))->willReturn( $this->acceptLanguageHeaderValue);
        $request->withAttribute(Argument::exact( $this->request_attr_name ), Argument::type('string'))->willReturn( $result_request );
        $request_mock = $request->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response_mock = $response->reveal();

        $next = function($req, $res) { return $res; };

        $result_response = $sut($request_mock, $response_mock, $next);
        $this->assertInstanceOf(ResponseInterface::class, $result_response);
    }


    /**
     * @depends testInstantiation
     */
    public function testSinglePass( LanguageNegotiationMiddleware $sut )
    {

        $result_request = $this->psr17Factory
                               ->createServerRequest("GET", "https://httpbin.org/")
                               ->withHeader($this->acceptLanguageHeaderName, $this->acceptLanguageHeaderValue);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getHeaderLine(Argument::exact($this->acceptLanguageHeaderName))->willReturn( $this->acceptLanguageHeaderValue);
        $request->withAttribute(Argument::exact( $this->request_attr_name ), Argument::type('string'))->willReturn( $result_request );
        $request_mock = $request->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response_mock = $response->reveal();

        $handler = $this->prophesize( RequestHandlerInterface::class );
        $handler->handle( Argument::type(ServerRequestInterface::class) )->willReturn( $response_mock );
        $handler_mock = $handler->reveal();

        $result_response = $sut->process($request_mock, $handler_mock);
        $this->assertInstanceOf(ResponseInterface::class, $result_response);
    }


}
