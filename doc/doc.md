# Documentation

## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) which provides the http layer used to send requests to exchange rate services. This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). Below is an example using Guzzle6:

```bash
composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

## Usage

### Without a framework

### Creating

Swap supports chaining exchange rates services in case the previous one fails.

The code below will create a Swap instance with the `Fixer`, `Yahoo` and `Google` services added. It means rates
will be first fetched from `Fixer`, then `Yahoo`, then `Google` in case of failure.

```php
use Swap\Swap;

$swap = Swap::create()
    ->with('fixer')
    ->with('yahoo')
    ->with('google');
```

Here is the complete list of supported services and options:

```php
use Swap\Swap;

$swap = Swap::create()
    ->with('central_bank_of_czech_republic')
    ->with('central_bank_of_republic_turkey')
    ->with('currencylayer', ['access_key' => 'secret', 'enterprise' => false])
    ->with('european_central_bank')
    ->with('fixer')
    ->with('google')
    ->with('national_bank_of_romania')
    ->with('open_exchange_rates', ['app_id' => 'secret', 'enterprise' => false])
    ->with('array', [['EUR/USD' => new ExchangeRate('1.5')]])
    ->with('webservicex')
    ->with('xignite', ['token' => 'token'])
    ->with('yahoo');
```

### Quoting

Swap allows you to get the latest exchange rates but historical ones as well. Please check this list to see currencies supported
by your service and if it offers historical rates.

To retrieve the latest exchange rate for a currency pair, you need to use the `quote()` method.

```php
// Latest rate
$rate = $swap->latest('EUR/USD');

// 1.129
echo $rate->getValue();

// 2016-08-26
echo $rate->getDate()->format('Y-m-d');

// Historical rate
$rate = $swap->historical('EUR/USD', (new DateTime())->modify('-15 days'));
```

> Currencies are expressed as their [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) code.

### Caching

Swap provides a [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6) integration allowing you to cache rates during a given time using the adapter of your choice.

The following example uses the Apcu cache from [php-cache.com](http://php-cache.com) PSR-6 implementation installable using `composer require cache/apcu-adapter`.

```php
use Cache\Adapter\Apcu\ApcuCachePool;

$swap = Swap::create(new ApcuCachePool(), ['cache_ttl' => '3600']);
```

All rates will now be cached in Apcu during 3600 seconds.

You can also control the cache per currency query:

```php
// Overrides the cache ttl for this query
$rate = $swap->latest('EUR/USD', ['cache_ttl' => 60]);

// Gets a refreshed rate
$rate = $swap->latest('EUR/USD', ['refresh' => true]);
```
