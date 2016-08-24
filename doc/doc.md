# Documentation

## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) which provides the http layer used to send requests to exchange rate services. This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). Below is an example using Guzzle6:

```bash
composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

## Usage

```php
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Swap\Provider\YahooFinanceProvider;
use Swap\Swap;

// Instantiate your Http Adapter
$httpAdapter = new GuzzleClient();

// Create the Yahoo Finance provider
$yahooProvider = new YahooFinanceProvider($httpAdapter);

// Create Swap with the provider
$swap = new Swap($yahooProvider);
```

### Quoting

To retrieve the latest exchange rate for a currency pair, you need to use the `quote()` method.

```php
$rate = $swap->quote('EUR/USD');

// 1.187220
echo $rate;

// 1.187220
echo $rate->getValue();

// 15-01-11 21:30:00
echo $rate->getDate()->format('Y-m-d H:i:s');
```

> Currencies are expressed as their [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) code.

### Chaining providers

It is possible to chain providers in order to use fallbacks in case the main providers don't support the currency or are unavailable.
Simply create a `ChainProvider` wrapping the providers you want to chain.

```php
use Swap\Provider\ChainProvider;
use Swap\Provider\YahooFinanceProvider;

$chainProvider = new ChainProvider([
    new YahooFinanceProvider($httpAdapter),
    new GoogleFinanceProvider($httpAdapter)
]);
```

The rates will be first fetched using the Yahoo Finance provider and will fallback to Google Finance.

### Caching

#### Rates Caching

Swap provides a [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6) integration allowing you to cache rates during a given time using the adapter of your choice.

#### Example

The following example uses the Apcu cache from [php-cache.com](http://php-cache.com) PSR-6 implementation installable using `composer require cache/apcu-adapter`.

```php
use Cache\Adapter\Apcu\ApcuCachePool;
use Swap\Swap;

$cachePool = new ApcuCachePool();
$swap = new Swap($yahooProvider, $cachePool, 3600);
```

All rates will now be cached in Apcu during 3600 seconds.

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
$httpAdapter = new PluginClient(new GuzzleClient(), [$cachePlugin]);

$yahooProvider = new EuropeanCentralBankProvider($httpAdapter);

$swap = new Swap($yahooProvider);

// A http request is sent
$rate = $swap->quote('EUR/USD');

// A new request won't be sent
$rate = $swap->quote('EUR/GBP');
```
