# Documentation

## Sponsors

<table>
   <tr>
      <td><img src="https://s3.amazonaws.com/swap.assets/fixer_icon.png?v=2" width="50px"/></td>
      <td><a href="https://fixer.io">Fixer</a> is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.</td>
   </tr>
   <tr>
     <td><img src="https://s3.amazonaws.com/swap.assets/currencylayer_icon.png" width="50px"/></td>
     <td><a href="https://currencylayer.com">currencylayer</a> provides reliable exchange rates and currency conversions for your business up to 168 world currencies.</td>
   </tr>
</table>

## Index
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
  * [Retrieving Rates](#retrieving-rates)
  * [Rate Provider](#rate-provider)
* [Cache](#cache)
 * [Rates Caching](#rates-caching)
 * [Query Cache Options](#query-cache-options)
 * [Requests Caching](#requests-caching)
* [Creating a Service](#creating-a-service)
  * [Standard Service](#standard-service)
  * [Historical Service](#historical-service)
* [Supported Services](#supported-services)  
  
## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) 
which provides the http layer used to send requests to exchange rate services. 
This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). 
Below is an example using the curl client:

```bash
composer require php-http/curl-client nyholm/psr7 php-http/message florianv/swap
```

## Configuration

Before starting to retrieve currency exchange rates, we need to build `Swap`. Fortunately, the `Builder` class helps us to perform this task.

Let's say we want to use the [Fixer.io](http://fixer.io) service and fallback to [currencylayer](https://currencylayer.com) in case of failure. We would write the following:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('fixer', ['access_key' => 'your-access-key'])
    ->add('currency_layer', ['access_key' => 'secret', 'enterprise' => false])
    ->build();
```

As you can see, you can use the `add()` method to add a service. You can add as many as you want, they will be called in a chain, in case of failure.

We recommend to use one of the [services that support our project](#sponsors), providing a free plan up to 1,000 requests per day.

The complete list of all supported services is available [here](#supported-services).

## Usage

### Retrieving Rates

In order to get rates, you can use the `latest()` or `historical()` methods on `Swap`:

```php
// Latest rate
$rate = $swap->latest('EUR/USD');

// 1.129
echo $rate->getValue();

// 2016-08-26
echo $rate->getDate()->format('Y-m-d');

// Historical rate
$rate = $swap->historical('EUR/USD', (new \DateTime())->modify('-15 days'));
```

> Currencies are expressed as their [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) code.

### Rate provider

When using the chain service, it can be useful to know which service provided the rate.

You can use the `getProviderName()` function on a rate that gives you the name of the service that returned it:

```php
$name = $rate->getProviderName();
```

For example, if Fixer returned the rate, it will be identical to `fixer`.

## Cache

### Rates Caching

`Exchanger` provides a [PSR-16 Simple Cache](http://www.php-fig.org/psr/psr-16) integration allowing you to cache rates during a given time using the adapter of your choice.

The following example uses the `Predis` cache from [php-cache.com](http://php-cache.com) PSR-6 implementation installable using `composer require cache/predis-adapter`.

You will also need to install a "bridge" that allows to adapt the PSR-6 adapters to PSR-16 using `composer require cache/simple-cache-bridge` (https://github.com/php-cache/simple-cache-bridge).
 
```bash
 $ composer require cache/predis-adapter
 ```

```php
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;

$client = new \Predis\Client('tcp:/127.0.0.1:6379');
$psr6pool = new PredisCachePool($client);
$simpleCache = new SimpleCacheBridge($psr6pool);

$swap = (new Builder(['cache_ttl' => 3600, 'cache_key_prefix' => 'myapp-']))
    ->useSimpleCache($simpleCache)
    ->build();
```

All rates will now be cached in Redis during 3600 seconds, and cache keys will be prefixed with 'myapp-'

### Query Cache Options

You can override `Swap` caching options per request.

#### cache_ttl

Set cache TTL in seconds. Default: `null` - cache entries permanently

```php
// Override the global cache_ttl only for this query
$rate = $swap->latest('EUR/USD', ['cache_ttl' => 60]);
$rate = $swap->historical('EUR/USD', $date, ['cache_ttl' => 60]);
```

#### cache

Disable/Enable caching. Default: `true`

```php
// Disable caching for this query
$rate = $swap->latest('EUR/USD', ['cache' => false]);
$rate = $swap->historical('EUR/USD', $date, ['cache' => false]);
```

#### cache_key_prefix

Set the cache key prefix. Default: empty string

There is a limitation of 64 characters for the key length in PSR-6, because of this, key prefix must not exceed 24 characters, as sha1() hash takes 40 symbols.

PSR-6 do not allows characters `{}()/\@:` in key, these characters are replaced with `-`

```php
// Override cache key prefix for this query
$rate = $swap->latest('EUR/USD', ['cache_key_prefix' => 'currencies-special-']);
$rate = $swap->historical('EUR/USD', $date, ['cache_key_prefix' => 'currencies-special-']);
```

### Requests Caching

By default, `Swap` queries the service for each rate you request, but some services like `Fixer` sends a whole file containing
rates for each base currency. 

It means that if you are requesting multiple rates using the same base currency like `EUR/USD`, then `EUR/GBP`, you may want
to cache these responses in order to improve performances.

#### Example

Install the PHP HTTP Cache plugin and the PHP Cache Array adapter.

```bash
$ composer require php-http/cache-plugin cache/array-adapter
```

Modify the way you create your HTTP Client by decorating it with a `PluginClient` using the `Array` cache:

```php
use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Swap\Builder;

$pool = new ArrayCachePool();
$streamFactory = new GuzzleStreamFactory();
$cachePlugin = new CachePlugin($pool, $streamFactory);
$client = new PluginClient(new GuzzleClient(), [$cachePlugin]);

$swap = (new Builder())
    ->useHttpClient($client)
    ->add('fixer', ['access_key' => 'your-access-key'])
    ->add('currency_layer', ['access_key' => 'secret', 'enterprise' => false])
    ->build();

// A http request is sent
$rate = $swap->latest('EUR/USD');

// A new request won't be sent
$rate = $swap->latest('EUR/GBP');
```

## Creating a Service

You want to add a new service to `Swap` ? Great!

If your service must send http requests to retrieve rates, your class must extend the `HttpService` class, otherwise you can extend the more generic `Service` class.

### Standard service

In the following example, we are creating a `Constant` service that returns a constant rate value.

```php
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRate;
use Exchanger\Service\HttpService;
use Swap\Service\Registry;

class ConstantService extends HttpService
{
    /**
     * Gets the exchange rate.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        // If you want to make a request you can use
        // $content = $this->request('http://example.com');

        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    /**
     * Processes the service options.
     *
     * @param array &$options
     *
     * @return void
     */
    public function processOptions(array &$options): void
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
    public function supportQuery(ExchangeRateQuery $exchangeQuery): bool
    {
        // For example, our service only supports EUR as base currency
        return 'EUR' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    /**
     * Gets the name of the exchange rate service.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'constant';
    }
}

// Register the service so it's available using Builder::add()
Registry::register('constant', ConstantService::class);

// Now you can use the add() method add your service name and pass your options
$swap = (new Builder())
    ->add('constant', ['value' => 10])
    ->build();

// 10
echo $swap->latest('EUR/USD')->getValue();
```

### Historical service

If your service supports retrieving historical rates, you need to use the `SupportsHistoricalQueries` trait.

You will need to rename the `getExchangeRate` method to `getLatestExchangeRate` and switch its visibility to protected, and implement a new `getHistoricalExchangeRate` method:

```php
use Exchanger\Service\SupportsHistoricalQueries;

class ConstantService extends HttpService
{
    use SupportsHistoricalQueries;
    
    /**
     * Gets the exchange rate.
     *
     * @param ExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    protected function getLatestExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    /**
     * Gets an historical rate.
     *
     * @param HistoricalExchangeRateQuery $exchangeQuery
     *
     * @return ExchangeRate
     */
    protected function getHistoricalExchangeRate(HistoricalExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }
}    
```

## Supported Services

Here is the complete list of supported services and their possible configurations:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('fixer', ['access_key' => 'your-access-key'])
    ->add('currency_layer', ['access_key' => 'secret', 'enterprise' => false])
    ->add('coin_layer', ['access_key' => 'secret', 'paid' => false])
    ->add('european_central_bank')
    ->add('abstract_api', ['api_key' => 'secret'])
    ->add('exchange_rates_api')
    ->add('national_bank_of_romania')
    ->add('central_bank_of_republic_turkey')
    ->add('central_bank_of_czech_republic')
    ->add('russian_central_bank')
    ->add('bulgarian_national_bank')
    ->add('webservicex')
    ->add('forge', ['api_key' => 'secret'])
    ->add('cryptonator')
    ->add('currency_data_feed', ['api_key' => 'secret'])
    ->add('currency_converter', ['access_key' => 'secret', 'enterprise' => false])
    ->add('open_exchange_rates', ['app_id' => 'secret', 'enterprise' => false])
    ->add('xignite', ['token' => 'token'])
    ->add('xchangeapi', ['api-key' => 'secret'])
    ->add('array', [
        [
            'EUR/USD' => 1.1,
            'EUR/GBP' => 1.5
        ],
        [
            '2017-01-01' => [
                'EUR/USD' => 1.5
            ],
            '2017-01-03' => [
                'EUR/GBP' => 1.3
            ],
        ]
    ])
    ->build();
```
