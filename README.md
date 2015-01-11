# Swap [![Build status][travis-image]][travis-url] [![Version][version-image]][version-url]

> Exchange rates library for PHP 5.4+

## Installation

```bash
$ composer require florianv/swap
```

## Usage

In order to retrieve exchange rates, you need to get an instance of the `Swap` service and add a provider to it.
The `Builder` class provides a fluent interface to help you building it.

```php
$swap = (new \Swap\Builder())
    ->yahooFinanceProvider()
    ->build();
```

### Quoting

To retrieve the latest exchange rate for a currency pair, you can use the `quote()` method.

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

```php
$swap = (new \Swap\Builder())
    ->yahooFinanceProvider()
    ->googleFinanceProvider()
    ->build();
```

The rates will be first fetched using the Yahoo Finance provider and will fallback to Google Finance.

### Caching

For performance reasons you might want to cache the rates during a given time. Currently Swap allows you to use the 
[`doctrine/cache`](https://github.com/doctrine/cache) library as caching provider.

```php
$builder->doctrineCache(new \Doctrine\Common\Cache\ApcCache(), 3600);
```

All rates will now be cached in APC during 3600 seconds.

## Integrations

- A Symfony2 bundle [FlorianvSwapBundle](https://github.com/florianv/FlorianvSwapBundle).

## Providers

- [European Central Bank](http://www.ecb.europa.eu/home/html/index.en.html)
Supports only EUR as base currency.
- [Google Finance](http://www.google.com/finance)
Supports multiple currencies as base and quote currencies.
- [Open Exchange Rates](https://openexchangerates.org)
Supports only USD as base currency for the free version and multiple ones for the enterprise version.
- [Xignite](https://www.xignite.com)
You must have access to the `XigniteGlobalCurrencies` API.
Supports multiple currencies as base and quote currencies.
- [Yahoo Finance](https://finance.yahoo.com/)
Supports multiple currencies as base and quote currencies.
- [WebserviceX](http://www.webservicex.net/ws/default.aspx)
Supports multiple currencies as base and quote currencies.

## License

[MIT](https://github.com/florianv/swap/blob/master/LICENSE)

[travis-url]: https://travis-ci.org/florianv/swap
[travis-image]: http://img.shields.io/travis/florianv/swap.svg?style=flat

[version-url]: https://packagist.org/packages/florianv/swap
[version-image]: http://img.shields.io/packagist/v/florianv/swap.svg?style=flat
