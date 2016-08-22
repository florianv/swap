# Documentation

## Installation

Swap makes use of different third party libraries like [HTTPlug](https://github.com/php-http/httplug) which provides
the http layer used to send requests to exchange rate services.

In order to use it, you need to install an HTTP Adapter and the [HTTP Message](https://github.com/php-http/message) package.

For example, if you want to use [Guzzle 6](http://docs.guzzlephp.org/en/latest/index.html) as adapter, your `composer.json` must contain:

```json
{
    "require": {
        "php-http/message": "^1.0",
        "php-http/guzzle6-adapter": "^1.0",
        "florianv/swap": "~3.0"
    }
}
```

## Usage

First, you need to instantiate your Http Adapter (Guzzle is used in the example):

```php
$httpAdapter = new \Http\Adapter\Guzzle6\Client();

// You might have to disable SSL verification in Guzzle or provide a path to your cert
// See Guzzle's documentation http://docs.guzzlephp.org/en/latest/request-options.html#verify
// $httpAdapter = \Http\Adapter\Guzzle6\Client::createWithConfig(['verify' => false]);
```

Then, you can create a provider and add it to Swap:

```php
// Create the Yahoo Finance provider
$yahooProvider = new \Swap\Provider\YahooFinanceProvider($httpAdapter);

// Create Swap with the provider
$swap = new \Swap\Swap($yahooProvider);
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
$chainProvider = new \Swap\Provider\ChainProvider([
    new \Swap\Provider\YahooFinanceProvider($httpAdapter),
    new \Swap\Provider\GoogleFinanceProvider($httpAdapter)
]);
```

The rates will be first fetched using the Yahoo Finance provider and will fallback to Google Finance.

### Caching

Swap provides a [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6) integration allowing you to cache rates during a given time using the adapter of your choice.

#### Example

The following example uses the [`cache/cache`](https://github.com/php-cache/cache) PSR-6 implementation installable using `composer require cache/cache`.

```php
$cachePool = new \Cache\Adapter\Apcu\ApcuCachePool();

$swap = new \Swap\Swap($yahooProvider, $cachePool, 3600);
```

All rates will now be cached in Apcu during 3600 seconds.

### Currency Codes

Swap provides an enumeration of currency codes so you can use autocompletion to avoid typos.

```php
use \Swap\Util\CurrencyCodes;

// Retrieving the EUR/USD rate
$rate = $swap->quote(new \Swap\Model\CurrencyPair(
    CurrencyCodes::ISO_EUR,
    CurrencyCodes::ISO_USD
));
```
