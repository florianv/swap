# Documentation

## Usage

First, you need to create an HTTP adapter provided by the [egeloen/ivory-http-adapter](https://github.com/egeloen/ivory-http-adapter)
library.

```php
$httpAdapter = new \Ivory\HttpAdapter\FileGetContentsHttpAdapter();
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

For performance reasons you might want to cache the rates during a given time.

#### Doctrine Cache

##### Installation

```bash
$ composer require doctrine/cache
```

##### Usage

```php
// Create the cache adapter
$cache = new \Swap\Cache\DoctrineCache(new \Doctrine\Common\Cache\ApcCache(), 3600);

// Pass the cache to Swap
$swap = new \Swap\Swap($provider, $cache);
```

All rates will now be cached in APC during 3600 seconds.

#### Illuminate Cache

##### Installation

```bash
$ composer require illuminate/cache
```

##### Usage

```php
// Create the cache adapter
$store = new \Illuminate\Cache\ApcStore(new \Illuminate\Cache\ApcWrapper());
$cache = new \Swap\Cache\IlluminateCache($store, 60);

// Pass the cache to Swap
$swap = new \Swap\Swap($provider, $cache);
```

All rates will now be cached in APC during 60 minutes.

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
