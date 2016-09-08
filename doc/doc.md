# Documentation

## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) which provides the http layer used to send requests to exchange rate services. This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). Below is an example using Guzzle6:

```bash
composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

## Configuration

In order to start working with `Swap`, you need to configure which service(s) you want to use.
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

As you can see, you can use the `with()` method to add a service to `Swap`.

You can add as many services as you want, they will be called in a chain, in case of failure.

## Usage

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

## Caching

### Rates Caching

`Swap` provides a [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6) integration allowing you to cache rates during a given time using the adapter of your choice.

The following example uses the Apcu cache from [php-cache.com](http://php-cache.com) PSR-6 implementation installable using `composer require cache/apcu-adapter`.

```php
use Cache\Adapter\Apcu\ApcuCachePool;

$swap = Swap::create(new ApcuCachePool(), ['cache_ttl' => '3600']);
```

All rates will now be cached in Apcu during 3600 seconds.

### Query Cache Control

You can also control the cache per currency query:

```php
// Overrides the cache ttl for this query
$rate = $swap->latest('EUR/USD', ['cache_ttl' => 60]);
$rate = $swap->historical('EUR/USD', $date, ['cache_ttl' => 60]);

// Gets a refreshed rate
$rate = $swap->latest('EUR/USD', ['refresh' => true]);
$rate = $swap->historical('EUR/USD', $date, ['refresh' => true]);
```

### Requests Caching

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

### Creating a Service

You want to add a new service to `Swap` ? Great!

First you must check if the service supports retrieval of historical rates. If it's the case, you must extend the `HistoricalService` class,
otherwise use the `Service` class.

In the following example, we are creating a `Constant` service that returns a constant rate value.

```php
use Exchanger\Swap\ServiceRegistry;
use Exchanger\Service\Service;
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Swap\Swap;
use Exchanger\ExchangeRate;

class ConstantService extends Service
{
    /**
     * Gets the exchange rate.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery)
    {
        // If you want to make a request you can use
        $content = $this->request('http://example.com');

        return new ExchangeRate($this->options['value']);
    }

    /**
     * Processes the service options.
     *
     * @param array &$options
     *
     * @return array
     */
    public function processOptions(array &$options)
    {
        if (!isset($options['value'])) {
            throw new \InvalidArgumentException('The "value" option must be provided.');
        }
    }

    /**
     * Tells if the service supports the exchange rate query.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return bool
     */
    public function supportQuery(ExchangeRateQuery $exchangeQuery)
    {
        // For example, our service only supports EUR as base currency
        return 'EUR' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }
}

// Register the service so it's available using Swap::with()
ServiceRegistry::register('constant', ConstantService::class);

// Now you can use the with() method with your service name and pass your options
$swap = Swap::create()->with('constant', ['value' => 10]);

// 10
echo $swap->latest('EUR/USD')->getValue();
```
