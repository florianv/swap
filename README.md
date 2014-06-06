# Swap [![Build status][travis-image]][travis-url] [![Insight][insight-image]][insight-url] [![Version][version-image]][version-url] [![License][license-image]][license-url]

Swap helps you to retrieve exchange rates from various providers. It leverages their ability to retrieve multiple quotes
at once, while simulating this behavior for those who don't support it by sending parallel HTTP requests.

> If you want to use this library with Symfony2, you can install [FlorianvSwapBundle] (https://github.com/florianv/FlorianvSwapBundle).

## Installation

Add this line to your `composer.json` file:

```json
{
    "require": {
        "florianv/swap": "~1.0"
    }
}
```

## Usage

First, you need to create a provider:

```php
$client = new \Guzzle\Http\Client();
$yahoo = new \Swap\Provider\YahooFinance($client);
```

Then you can add it to Swap:

```php
$swap = new \Swap\Swap();
$swap->addProvider($yahoo);
```

Your job is to create a currency pair and Swap will set its rate:

```php
// Create the currency pair EUR/USD
$pair = new \Swap\Model\CurrencyPair('EUR', 'USD');

// Quotes the pair
$swap->quote($pair);

// 1.3751
echo $pair->getRate();
```

We created a currency pair `EUR/USD`, quoted it with the `YahooFinance` provider and got `1.3751` as rate
which means that `1 EUR` is exchanged for `1.3751 USD`.

> Currencies are expressed as their [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) code.

### Multiple pairs

You can also quote multiple pairs at once:

```php
use Swap\Model\CurrencyPair;

$eurUsd = new CurrencyPair('EUR', 'USD');
$usdGbp = new CurrencyPair('USD', 'GBP');
$gbpJpy = new CurrencyPair('GBP', 'JPY');

$swap->quote(array($eurUsd, $usdGbp, $gbpJpy));

// 1.3751
echo $eurUsd->getRate();

// 0.5938
echo $usdGbp->getRate();

// 171.5772
echo $gbpJpy->getRate();
```

### Date

You can also retrieve the date at which the rate was calculated:

```php
// $date is a \DateTime instance
$date = $pair->getDate()
```

### Chained providers

Providers can be chained so when one of them fails, the next one can be used to quote the pairs
that were not processed.

```php
$yahoo = new \Swap\Provider\YahooFinance($client);
$google = new \Swap\Provider\GoogleFinance($client);

$swap->addProvider($yahoo);
$swap->addProvider($google);
```

### Exception Handling

Swap throws different types of exceptions:

```php
try {
    $swap->quote($pair);
} catch (\Swap\Exception\QuotationException $e) {

    // Default exception when the quote operation failed
    // For example when the HTTP request failed

} catch (\Swap\Exception\UnsupportedBaseCurrencyException $e) {

    // Exception thrown when a currency is not supported as base by the provider

} catch (\Swap\Exception\UnsupportedCurrencyPairException $e) {

    // Exception thrown when the currency pair is not supported
}
```

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
[travis-image]: https://travis-ci.org/florianv/swap.svg?branch=master

[insight-url]: https://insight.sensiolabs.com/projects/825d1c3f-839b-47e6-969a-7ddefffe94b1
[insight-image]: https://insight.sensiolabs.com/projects/825d1c3f-839b-47e6-969a-7ddefffe94b1/mini.png

[license-url]: https://packagist.org/packages/florianv/swap
[license-image]: http://img.shields.io/packagist/l/florianv/swap.svg

[version-url]: https://packagist.org/packages/florianv/swap
[version-image]: http://img.shields.io/packagist/v/florianv/swap.svg
