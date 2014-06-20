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

Currently Guzzle 3 and 4 are supported HTTP clients, so you will need to require one of them too.

- `"guzzle/guzzle": "~3.0"`
- `"guzzlehttp/guzzle": "~4.0"`

## Usage

First, you need to create an adapter:

```php
// Creating a Guzzle 3 adapter
$adapter = new \Swap\Adapter\Guzzle3Adapter(new \Guzzle\Http\Client());

// Creating a Guzzle 4 adapter
$adapter = new \Swap\Adapter\Guzzle4Adapter(new \GuzzleHttp\Client());
```

> For BC reasons, it is still possible to pass $adapter = new \Guzzle\Http\Client(); as adapter
> but it will be removed in version 2.0.

Then, you can create a provider and add it to Swap:

```php
// Creating a YahooFinance provider
$yahoo = new \Swap\Provider\YahooFinance($adapter);

// Instantiating Swap and adding the provider
$swap = new \Swap\Swap();
$swap->addProvider($yahoo);
```

Now, your job is to create a currency pair and Swap will set its rate:

```php
// Creating the currency pair EUR/USD
$pair = \Swap\Model\CurrencyPair::createFromString('EUR/USD');

// Quoting the pair
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

$eurUsd = CurrencyPair::createFromString('EUR/USD');
$usdGbp = CurrencyPair::createFromString('USD/GBP');
$gbpJpy = CurrencyPair::createFromString('GBP/JPY');

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
