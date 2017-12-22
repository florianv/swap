# Documentation

## Index
* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Cache](#cache)
 * [Rates Caching](#rates-caching)
  * [Cache Options](#cache-options)
 * [Requests Caching](#requests-caching)
* [Service](#service)
  * [Creating a Service](#creating-a-service)
  * [Supported Services](#supported-services)  
   
## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) 
which provides the http layer used to send requests to exchange rate services. 
This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). 
Below is an example using [Guzzle 6](http://docs.guzzlephp.org/en/latest/index.html):

```bash
composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

## Configuration

Before starting to retrieve currency exchange rates, we need to build `Swap`. Fortunately, the `Builder` class helps us to perform this task.

Let's say we want to use the [Fixer.io](http://fixer.io) service and fallback to [Google](https://google.com) in case of failure. We would write the following:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('fixer')
    ->add('google')
    ->build();
```

As you can see, you can use the `add()` method to add a service. You can add as many as you want, they will be called in a chain, in case of failure.

> You can consult the list of the supported services and their options [here](#supported-services)

## Usage

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

## Cache

### Rates Caching

`Swap` provides a [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6) integration allowing you to cache rates during a given time using the adapter of your choice.

The following example uses the Apcu cache from [php-cache.com](http://php-cache.com) PSR-6 implementation.
 
```bash
 $ composer require cache/apcu-adapter
 ```

```php
use Cache\Adapter\Apcu\ApcuCachePool;

$swap = (new Builder(['cache_ttl' => 60]))
    ->useCacheItemPool(new ApcuCachePool())
    ->build();
```

All rates will now be cached in Apcu during 60 seconds.

### Cache Options

You can override `Swap` caching per request:

```php
// Overrides the global cache ttl to 60 seconds
$rate = $swap->latest('EUR/USD', ['cache_ttl' => 60]);
$rate = $swap->historical('EUR/USD', $date, ['cache_ttl' => 60]);

// Disable the cache
$rate = $swap->latest('EUR/USD', ['cache' => false]);
$rate = $swap->historical('EUR/USD', $date, ['cache' => false]);
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
    ->add('fixer')
    ->build();

// A http request is sent
$rate = $swap->latest('EUR/USD');

// A new request won't be sent
$rate = $swap->latest('EUR/GBP');
```

## Service

### Creating a Service

You want to add a new service to `Swap` ? Great!

First you must check if the service supports retrieval of historical rates. If it's the case, you must extend the `HistoricalService` class,
otherwise use the `Service` class.

In the following example, we are creating a `Constant` service that returns a constant rate value.

```php
use Exchanger\Service\Service;
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\ExchangeRate;
use Swap\Service\Registry;
use Swap\Builder;

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

// Register the service so it's available using Builder::add()
Registry::register('constant', ConstantService::class);

// Now you can use the add() method add your service name and pass your options
$swap = (new Builder())
    ->add('constant', ['value' => 10])
    ->build();

// 10
echo $swap->latest('EUR/USD')->getValue();
```

## Supported Services

Here is the complete list of supported services and their possible configurations:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('central_bank_of_czech_republic')
    ->add('central_bank_of_republic_turkey')
    ->add('currency_data_feed', ['api_key' => 'secret'])
    ->add('currency_layer', ['access_key' => 'secret', 'enterprise' => false])
    ->add('european_central_bank')
    ->add('fixer')
    ->add('forge', ['api_key' => 'secret'])
    ->add('google')
    ->add('national_bank_of_romania')
    ->add('open_exchange_rates', ['app_id' => 'secret', 'enterprise' => false])
    ->add('array', [
        [
            'EUR/USD' => new ExchangeRate('1.1'),
            'EUR/GBP' => 1.5
        ],
        [
            '2017-01-01' => [
                'EUR/USD' => new ExchangeRate('1.5')
            ],
            '2017-01-03' => [
                'EUR/GBP' => 1.3
            ],
        ]
    ])
    ->add('webservicex')
    ->add('xignite', ['token' => 'token'])
    ->add('russian_central_bank')
    ->add('cryptonator')
    ->build();
```
