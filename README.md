<img src="https://static.germania-kg.com/logos/ga-logo-2016-web.svgz" width="250px">

------



# Germania KG · Language Negotiation

This middleware stores the result of Will Durand's language [Negotiation](https://github.com/willdurand/Negotiation) language in a Request attribute `X-language-negotiated`. 

Works with both **PSR-15 *SinglePass*** and traditional **Slim-like *DoublePass*** approach.

## Usage

### Setup

```php
<?php
use Germania\LanguageNegotiation\LanguageNegotiationMiddleware;
use Negotiation\LanguageNegotiator;

$negotiator = new LanguageNegotiator();  
$priorities = array('de', 'fu', 'en');

new LanguageNegotiationMiddleware($negotiator, $priorities);
```

**Configuration using constructor :**

```php
// Defaults:
$accept_header = "Accept-Language";
$attr_name = "X-language-negotiated";
$logger = new Monolog // Any PSR-3 Logger; 

new LanguageNegotiationMiddleware($negotiator, $priorities, $accept_header, $attr_name, $logger);
```

**Configuration using methods API:**

```php
$middleware = new LanguageNegotiationMiddleware($negotiator, $priorities);
$middleware->setAcceptLanguageHeader("Accept-Language");
$middleware->setRequestAttributeName("X-language-negotiated");
$middleware->setLogger( $psr3_logger );
```



### Usage in Controller

Slim-like example:

```php
<?php
class MyController
{
  public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args)
  {
    	// S.th. like 'fu'
    	echo $request->getAttribute("X-language-negotiated");
  }
}
```



## Development

```bash
$ git clone git@github.com:GermaniaKG/language-negotiation.git
$ cd language-negotiation
$ composer install
```



## Unit tests

Either copy `phpunit.xml.dist` to `phpunit.xml` and adapt to your needs, or leave as is. Run [PhpUnit](https://phpunit.de/) test or composer scripts like this:

```bash
$ composer test
# or
$ vendor/bin/phpunit
```

