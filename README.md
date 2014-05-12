Swap
====

Swap is an exchange rates library for PHP 5.3+. It offers multiple providers and leverage their ability
to retrieve multiple quotes at once, while simulating this behavior for those who don't support it
by using Guzzle's parallel HTTP requests.

## Installation

The recommended way to install Swap is via [Composer](https://getcomposer.org).

Add this line to your `composer.json` file:

```json
{
    "require": {
        "florianv/swap": "~1.0"
    }
}
```

Tell Composer to update the dependency by running:

```bash
$ php composer.phar update florianv/swap
```

## Usage

Firstly, you need to create an HTTP client:

```php
$client = new \Guzzle\Http\Client();
```

Then you can create a provider:

```php
$yahoo = new \Swap\Provider\YahooFinance($client);
```

Create a Swap instance and add the provider:

```php
$swap = new Swap\Swap();
$swap->addProvider($yahoo);
```

Your job is to create a currency pair with a base and quote currencies exprimed as their
[ISO 4217](http://fr.wikipedia.org/wiki/ISO_4217) code, stick it to Swap and it will set its rate.

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

It is possible to retrieve the date at which the rate was calculated. If the provider does not give
this information, it will be set to now.

```php
// $date is a \DateTime instance
$date = $pair->getDate()
```

### Chained providers

Providers can be chained. When an exception is thrown by a provider, the following will be used
to quote the pairs that were not quoted.

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

Supports multiple currencies as base and quote currencies.

- [Yahoo Finance](https://fr.finance.yahoo.com)

Supports multiple currencies as base and quote currencies.

## License

[MIT](https://github.com/florianv/swap/blob/master/LICENSE)
