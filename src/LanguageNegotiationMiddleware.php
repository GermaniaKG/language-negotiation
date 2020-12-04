<?php
namespace Germania\LanguageNegotiation;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;

use Negotiation\AbstractNegotiator;

use Negotiation\Exception\Exception as NegotiationExceptionInterface;


/**
 * This middleware uses William Durand's LanguageNegotiator
 * to store the client's preferred language in a PSR-7 request attribute `X-language-negotiated`.
 *
 * If the negotiation fails, the first language from the "priorities" will be used.
 */
class LanguageNegotiationMiddleware implements MiddlewareInterface
{
    use LoggerAwareTrait;

    /**
     * @var LanguageNegotiator
     */
    public $negotiator;


    /**
     * Request attribute name with language negotiation result
     * @var string
     */
    public $request_attribute_name = 'X-language-negotiated';


    /**
     * Request header name
     * @var string
     */
    public $accept_language_header = 'Accept-Language';


    /**
     * @var array
     */
    public $priorities = array();


    /**
     * @var string
     */
    public $default_language;


    /**
     * PSR-3 Loglevel name
     * @var string
     */
    public $loglevel_success = "info";



    /**
     * @param AbstractNegotiator   $negotiator             Will Durand's Language Negotiator
     * @param string[]             $priorities             Array with languages, e.g. `["de", "fr"]`
     * @param string               $accept_language_header  Optional: Custom accept header name
     * @param string               $request_attribute_name Optional: Custom request attribute name
     * @param LoggerInterface|null $logger                 Optional: PSR-3 Logger
     */
    public function __construct( AbstractNegotiator $negotiator, array $priorities, ?string $accept_language_header, ?string $request_attribute_name, ?LoggerInterface $logger)
    {
        $this->setNegotiator( $negotiator );
        $this->setPriorities( $priorities );
        $this->setAcceptLanguageHeader( $accept_language_header ?: $this->accept_language_header);
        $this->setRequestAttributeName( $request_attribute_name ?: $this->request_attribute_name );
        $this->setLogger( $logger ?: new NullLogger );
    }



    /**
     * PSR-15 Single Pass
     *
     * @param  ServerRequestInterface  $request Server reuest instance
     * @param  RequestHandlerInterface $handler Request handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $request = $this->doBusiness($request);
        $response = $handler->handle($request);
        return $response;
    }



    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke( ServerRequestInterface $request, ResponseInterface $response, callable $next )
    {

        $request = $this->doBusiness($request);
        $response = $next($request, $response);

        return $response;
    }



    /**
     * @param  ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function doBusiness(ServerRequestInterface $request) : ServerRequestInterface
    {
        $this->logger->debug("Language-negotiation settings", [
            'priorities'       => $this->priorities,
            'acceptField'      => $this->accept_language_header,
            'requestAttribute' => $this->request_attribute_name
        ]);


        // Read accept language
        try {
            $accept_language_header = $request->getHeaderLine( $this->accept_language_header ) ?: null;
            $best_language_type = $this->negotiate($accept_language_header);
        }
        catch (\Throwable $e) {
            $best_language_type = $this->default_language;
            $this->logger->warning("Throwable caught, use default language instead", [
                'exceptionClass' => get_class($e),
                'exceptionMessage' => $e->getMessage(),
                'exceptionLocation' => $e->getFile() . ':' . $e->getLine(),
                'defaultLang' => $best_language_type,
            ]);
        }


        // Store language in Request attribute and return new
        $request = $request->withAttribute($this->request_attribute_name, $best_language_type);
        return $request;
    }



    /**
     * Performs the negotiation.
     *
     * @param  string $accept_language_header
     * @return string
     */
    public function negotiate( ?string $accept_language_header) : string
    {
        if (empty($accept_language_header)) {
            $this->logger->log($this->loglevel_success, "Accept-Language header is empty, use default language instead", [
                'defaultLang' => $this->default_language,
            ]);
            return $this->default_language;
        }


        $bestLanguage = $this->negotiator->getBest($accept_language_header, $this->priorities);

        if ($bestLanguage):
            $best_language_type = $bestLanguage->getType(); // s.th. like 'fu';
            $this->logger->log($this->loglevel_success, "Language-negotiation result", [
                'bestLang' => $best_language_type
            ]);
            return $best_language_type;
        endif;


        $this->logger->log($this->loglevel_success, "Language-negotiation gained no result, use default language instead", [
            'defaultLang' => $this->default_language,
            'negotiationResult' => gettype( $bestLanguage )
        ]);

        return $this->default_language;
    }





    /**
     * @param AbstractNegotiator $negotiator
     */
    public function setNegotiator( AbstractNegotiator $negotiator) : self
    {
        $this->negotiator = $negotiator;
        return $this;
    }


    /**
     * @param array $priorities
     */
    public function setPriorities( array $priorities) : self
    {
        $this->priorities = $priorities;
        $this->default_language = $this->priorities[0];
        return $this;
    }


    /**
     * @param string $accept_language_header Accept language header
     */
    public function setAcceptLanguageHeader( string $accept_language_header) : self
    {
        $this->accept_language_header = $accept_language_header;
        return $this;
    }


    /**
     * @param string $request_attribute_name Request attribute name
     */
    public function setRequestAttributeName( string $request_attribute_name) : self
    {
        $this->request_attribute_name = $request_attribute_name;
        return $this;
    }

}
