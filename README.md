<img src="https://static.germania-kg.com/logos/ga-logo-2016-web.svgz" width="250px">

------



# Germania KG · Language Negotiation

This middleware negotiates Client's preferred language (just like Will Durand's [Negotiation](https://github.com/willdurand/Negotiation)) but stores the language in a Request attribute `X-language-negotiated`. 

Works with both **PSR-15 *SinglePass*** and traditional **Slim-like *DoublePass*** approach.

## Usage

### Setup

```php
<?php
use Germania\LanguageNegotiation\LanguageNegotiationMiddleware;
use Negotiation\LanguageNegotiator;

$negotiator = new LanguageNegotiator();  
$priorities = array('de', 'fu', 'en');

new LanguageNegotiationMiddleware($negotiator, $priorities, null, null, null);
```

**Optional settings:**

These are the default settings:

```php
// Defaults:
$custom_header_name = "Accept-Language";
$custom_attr_name = "X-language-negotiated";

// Any PSR-3 Logger will do
$logger = new Monolog; 

new LanguageNegotiationMiddleware($negotiator, $priorities, $custom_header_name, $custom_attr_name, $logger);
```

**Configuration using methods API:**

```php
$middleware = new LanguageNegotiationMiddleware($negotiator, $priorities, null, null, null);
$middleware->setAcceptLanguageHeader("Accept-Language");
$middleware->setRequestAttributeName("X-language-negotiated");
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

