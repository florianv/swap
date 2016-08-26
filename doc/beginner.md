## Beginner

### Configuration

In order to start working with **Swap**, you need to configure which service(s) you want to use.
Here is the complete list of supported services:

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

As you can see, you can use the `with()` method to add a service to **Swap**.

You can add as many services as you want, they will be called in a chain, in case of failure.

### Usage

In order to get rates, you can use the `latest()` or `historical()` methods:

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

#### Rates Caching

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
$rate = $swap->historical('EUR/USD', $date, ['cache_ttl' => 60]);

// Gets a refreshed rate
$rate = $swap->latest('EUR/USD', ['refresh' => true]);
$rate = $swap->historical('EUR/USD', $date, ['refresh' => true]);
```

#### Requests Caching

By default, Swap queries the provider for each rate you request, but some providers like the `EuropeanCentralBankProvider`
return the same response no matter the requested currency pair. It means performances can be improved when using these providers
and when quoting multiple pairs during the same request.

#### Example

Install the PHP HTTP Cache plugin and the PHP Cache Array adapter `composer require php-http/cache-plugin cache/array-adapter`.

Modify the way you create your HTTP Client by decorating it with a `PluginClient` using the `Array` cache:

```php
use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Swap\Provider\EuropeanCentralBankProvider;
use Swap\Swap;

$pool = new ArrayCachePool();
$streamFactory = new GuzzleStreamFactory();
$cachePlugin = new CachePlugin($pool, $streamFactory);
$client = new PluginClient(new GuzzleClient(), [$cachePlugin]);

$swap = Swap::createWithClient($client)
    ->with('fixer');

// A http request is sent
$rate = $swap->quote('EUR/USD');

// A new request won't be sent
$rate = $swap->quote('EUR/GBP');
```
