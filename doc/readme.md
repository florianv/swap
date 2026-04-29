# Documentation

## 📘 About this documentation

This documentation covers the practical use of Swap, the PHP currency conversion library: configuration, caching, provider configuration, and how to write your own provider. For an overview of the library and the wider ecosystem (Exchanger, Laravel Swap, Symfony Swap), see the [README](../README.md).

## Index

* [Installation](#installation)
* [Configuration](#configuration)
  * [Building Swap](#building-swap)
  * [Adding multiple providers](#adding-multiple-providers)
  * [How the fallback chain works](#how-the-fallback-chain-works)
* [Usage](#usage)
  * [Retrieving rates](#retrieving-rates)
  * [Inspecting the rate](#inspecting-the-rate)
* [Caching](#caching)
  * [PSR-16 SimpleCache (minimal setup)](#psr-16-simplecache-minimal-setup)
  * [Per-query options](#per-query-options)
  * [HTTP request caching](#http-request-caching)
* [Provider configuration](#provider-configuration)
* [Creating a custom service](#creating-a-custom-service)
  * [Standard service](#standard-service)
  * [Historical service](#historical-service)
* [FAQ](#faq)

## 📦 Installation

Swap requires PHP 8.2 or newer. It does not bundle an HTTP client; any PSR-18 client paired with a PSR-17 request factory works, and `php-http/discovery` finds them automatically.

The simplest modern install:

```bash
composer require florianv/swap symfony/http-client nyholm/psr7
```

Other PSR-18 clients work the same way, for example Guzzle 7:

```bash
composer require florianv/swap php-http/guzzle7-adapter nyholm/psr7
```

You can also pass a client explicitly via `Builder::useHttpClient()` if you do not want auto-discovery.

## ⚙ Configuration

### Building Swap

`Swap` is built with the `Builder` class. The minimal case uses a single, free provider:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('european_central_bank')
    ->build();
```

`add()` registers a provider by its identifier (the string passed to `Builder::add()`, for example `european_central_bank`). The full list of identifiers is in the README's [Providers table](../README.md#providers).

### Adding multiple providers

You can chain several providers. Each one is configured with its own options array:

```php
use Swap\Builder;

$swap = (new Builder())
    ->add('your_primary_provider', ['api_key' => 'YOUR_KEY'])
    ->add('your_fallback_provider', ['api_key' => 'YOUR_KEY'])
    ->add('european_central_bank') // free fallback for EUR-base pairs
    ->build();
```

Identifiers and the configuration keys each one accepts are documented in the [Provider configuration](#provider-configuration) section.

### How the fallback chain works

Swap calls providers in declaration order. For each provider:

1. If the provider does not support the requested currency pair, it is skipped silently.
2. If the provider throws an exception, the exception is collected and the next provider is tried.
3. If a provider returns a rate, that rate is returned to the caller and the remaining providers are not called.

If every provider was skipped or threw, Swap raises an `Exchanger\Exception\ChainException` containing all collected exceptions. The chain does not retry the same provider, and there is no built-in delay between attempts.

## ⚡ Usage

### Retrieving rates

`Swap` exposes two methods, `latest()` for the most recent rate and `historical()` for a rate on a given date:

```php
// Latest rate
$rate = $swap->latest('EUR/USD');

echo $rate->getValue();                 // e.g. 1.0823
echo $rate->getDate()->format('Y-m-d'); // e.g. 2026-04-29

// Historical rate
$rate = $swap->historical('EUR/USD', (new \DateTime())->modify('-15 days'));
```

> Currencies are expressed as their [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) code.

Both methods accept an options array as a third argument; see [Per-query options](#per-query-options) for the supported keys.

### Inspecting the rate

The returned `Exchanger\Contract\ExchangeRate` exposes:

```php
$rate->getValue();         // float
$rate->getDate();          // DateTimeInterface
$rate->getCurrencyPair();  // Exchanger\CurrencyPair
$rate->getProviderName();  // string, the identifier that returned the rate
```

`getProviderName()` is useful when several providers are chained: the returned value is the identifier of the provider that actually answered, for example `european_central_bank`.

## 💾 Caching

### PSR-16 SimpleCache (minimal setup)

Swap caches results through a PSR-16 `SimpleCache`. Any PSR-16 implementation works. A minimal Symfony Cache example:

```bash
composer require symfony/cache
```

```php
use Swap\Builder;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

$cache = new Psr16Cache(new FilesystemAdapter());

$swap = (new Builder(['cache_ttl' => 3600, 'cache_key_prefix' => 'myapp-']))
    ->useSimpleCache($cache)
    ->add('european_central_bank')
    ->build();
```

All rates returned by Swap are now cached for 3600 seconds, keyed with the prefix `myapp-`.

If only PSR-6 adapters are available, you can bridge them to PSR-16 with [`cache/simple-cache-bridge`](https://github.com/php-cache/simple-cache-bridge). For example with Predis:

```bash
composer require cache/predis-adapter cache/simple-cache-bridge
```

```php
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;

$client = new \Predis\Client('tcp://127.0.0.1:6379');
$cache  = new SimpleCacheBridge(new PredisCachePool($client));
```

### Per-query options

Cache behavior can be overridden per call.

#### `cache_ttl`

Cache TTL in seconds. Default: `null` (cache entries do not expire).

```php
$rate = $swap->latest('EUR/USD', ['cache_ttl' => 60]);
$rate = $swap->historical('EUR/USD', $date, ['cache_ttl' => 60]);
```

#### `cache`

Disable or enable caching for a single query. Default: `true`.

```php
$rate = $swap->latest('EUR/USD', ['cache' => false]);
$rate = $swap->historical('EUR/USD', $date, ['cache' => false]);
```

#### `cache_key_prefix`

Override the cache key prefix for a single query. Default: empty string.

PSR-6 limits cache keys to 64 characters. The internal hash of the query takes 40 characters, so the prefix must not exceed 24 characters. PSR-6 also does not allow the characters `{}()/\@:` in keys; Swap replaces them with `-`.

```php
$rate = $swap->latest('EUR/USD', ['cache_key_prefix' => 'currencies-special-']);
$rate = $swap->historical('EUR/USD', $date, ['cache_key_prefix' => 'currencies-special-']);
```

### HTTP request caching

Some providers return all rates for a given base currency in a single response. If you fetch several pairs sharing the same base (for example `EUR/USD` and then `EUR/GBP`), caching the underlying HTTP response avoids hitting the provider twice.

Install the PHP HTTP cache plugin and a PSR-6 cache adapter:

```bash
composer require php-http/cache-plugin cache/array-adapter
```

Decorate your HTTP client with the cache plugin and pass it to `Builder::useHttpClient()`:

```php
use Cache\Adapter\PHPArray\ArrayCachePool;
use Http\Adapter\Guzzle7\Client as GuzzleClient;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Swap\Builder;

$pool          = new ArrayCachePool();
$streamFactory = new GuzzleStreamFactory();
$cachePlugin   = new CachePlugin($pool, $streamFactory);
$client        = new PluginClient(new GuzzleClient(), [$cachePlugin]);

$swap = (new Builder())
    ->useHttpClient($client)
    ->add('european_central_bank')
    ->build();

// First call performs an HTTP request
$rate = $swap->latest('EUR/USD');

// Second call hits the HTTP cache
$rate = $swap->latest('EUR/GBP');
```

## 🔑 Provider configuration

Public providers (central banks, national banks, `cryptonator`, `exchangeratehost`, `webservicex`) need no configuration. Add them by identifier:

```php
$swap = (new Builder())
    ->add('european_central_bank')
    ->add('national_bank_of_romania')
    ->build();
```

Commercial providers require an API key. The option name varies by provider:

| Identifier                       | Required option | Optional flags        |
| -------------------------------- | --------------- | --------------------- |
| `abstract_api`                   | `api_key`       |                       |
| `apilayer_currency_data`         | `api_key`       |                       |
| `apilayer_exchange_rates_data`   | `api_key`       |                       |
| `apilayer_fixer`                 | `api_key`       |                       |
| `coin_layer`                     | `access_key`    | `paid` (bool)         |
| `currency_converter`             | `access_key`    | `enterprise` (bool)   |
| `currency_data_feed`             | `api_key`       |                       |
| `currency_layer`                 | `access_key`    | `enterprise` (bool)   |
| `exchange_rates_api`             | `access_key`    |                       |
| `fastforex`                      | `api_key`       |                       |
| `fixer`                          | `access_key`    |                       |
| `fixer_apilayer`                 | `api_key`       |                       |
| `forge`                          | `api_key`       |                       |
| `open_exchange_rates`            | `app_id`        | `enterprise` (bool)   |
| `xchangeapi`                     | `api-key`       | (note the hyphen)     |
| `xignite`                        | `token`         |                       |

Example:

```php
$swap = (new Builder())
    ->add('apilayer_fixer',      ['api_key'    => 'YOUR_KEY'])
    ->add('open_exchange_rates', ['app_id'     => 'YOUR_APP_ID', 'enterprise' => false])
    ->add('xignite',             ['token'      => 'YOUR_TOKEN'])
    ->build();
```

The `array` provider is a special case used in tests and fixtures. It accepts a nested structure of latest and historical rates:

```php
$swap = (new Builder())
    ->add('array', [
        ['EUR/USD' => 1.1, 'EUR/GBP' => 1.5],          // latest rates
        ['2017-01-01' => ['EUR/USD' => 1.5]],          // historical rates
    ])
    ->build();
```

The full provider list with capabilities (base currency, quote currency, historical support) is in the README's [Providers table](../README.md#providers).

## 🧩 Creating a custom service

You can register your own provider as long as it implements the same contract used internally. If your service makes HTTP requests, extend `Exchanger\Service\HttpService`; otherwise extend the simpler `Exchanger\Service\Service`.

### Standard service

The example below registers a `Constant` service that returns a fixed rate value:

```php
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRate;
use Exchanger\Service\HttpService;
use Swap\Service\Registry;

class ConstantService extends HttpService
{
    public function getExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        // To call an HTTP endpoint:
        // $content = $this->request('https://example.com');

        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    public function processOptions(array &$options): void
    {
        if (!isset($options['value'])) {
            throw new \InvalidArgumentException('The "value" option must be provided.');
        }
    }

    public function supportQuery(ExchangeRateQuery $exchangeQuery): bool
    {
        // Example: only support EUR-base pairs.
        return 'EUR' === $exchangeQuery->getCurrencyPair()->getBaseCurrency();
    }

    public function getName(): string
    {
        return 'constant';
    }
}

// Register the service so Builder::add() recognizes its identifier
Registry::register('constant', ConstantService::class);

$swap = (new Builder())
    ->add('constant', ['value' => 10])
    ->build();

echo $swap->latest('EUR/USD')->getValue(); // 10
```

### Historical service

To support historical rates, use the `SupportsHistoricalQueries` trait. Rename `getExchangeRate` to `getLatestExchangeRate` (now `protected`) and implement `getHistoricalExchangeRate`:

```php
use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\Contract\ExchangeRate;
use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\Service\HttpService;
use Exchanger\Service\SupportsHistoricalQueries;

class ConstantService extends HttpService
{
    use SupportsHistoricalQueries;

    protected function getLatestExchangeRate(ExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }

    protected function getHistoricalExchangeRate(HistoricalExchangeRateQuery $exchangeQuery): ExchangeRate
    {
        return $this->createInstantRate($exchangeQuery->getCurrencyPair(), $this->options['value']);
    }
}
```

## ❓ FAQ

#### What happens when every provider fails?

Swap throws an `Exchanger\Exception\ChainException`. Calling `$exception->getExceptions()` on it returns the list of exceptions collected from each provider in the chain.

#### Can I use Swap without an API key?

Yes. The European Central Bank, the national banks, `cryptonator`, `exchangeratehost`, and `webservicex` do not require an API key. See the [Providers table](../README.md#providers) for the full list.

#### How do I cache rates?

Configure any PSR-16 cache via `Builder::useSimpleCache()`. See [PSR-16 SimpleCache (minimal setup)](#psr-16-simplecache-minimal-setup).

#### How do I disable cache for a single query?

Pass `['cache' => false]` as the options argument: `$swap->latest('EUR/USD', ['cache' => false])`.

#### How do I add my own provider?

Implement `Exchanger\Contract\ExchangeRateService` (or extend `HttpService` / `Service`), register it with `Swap\Service\Registry::register()`, then call `Builder::add()` with your identifier. See [Creating a custom service](#creating-a-custom-service).

#### Where is the full provider list with capabilities?

In the README's [Providers table](../README.md#providers). It lists every supported identifier with its base currency, quote currency, and historical support.
