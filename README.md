# <img src="https://s3.amazonaws.com/swap.assets/swap_logo.png" height="30px" width="30px"/> Swap

[![Build status](http://img.shields.io/travis/florianv/swap.svg?style=flat-square)](https://travis-ci.org/florianv/swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/florianv/swap.svg?style=flat-square)](https://scrutinizer-ci.com/g/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

Swap allows you to retrieve currency exchange rates from various services such as **[Fixer](https://fixer.io)**, **[currencylayer](https://currencylayer.com)** or **[1Forge](https://1forge.com)** 
and optionally cache the results. It is integrated to other libraries like [moneyphp/money](https://github.com/moneyphp/money) and provides
a [Symfony Bundle](https://github.com/florianv/FlorianvSwapBundle) and a [Laravel Package](https://github.com/florianv/laravel-swap).

## QuickStart

```bash
$ composer require florianv/swap php-http/message php-http/guzzle6-adapter ^1.0
```

```php
use Swap\Builder;

// Build Swap
$swap = (new Builder())

    // Use the Fixer.io service as first level provider
    ->add('fixer', ['access_key' => 'your-access-key'])
     
    // Use the currencylayer.com service as first fallback
    ->add('currency_layer', ['access_key' => 'secret', 'enterprise' => false])
     
    // Use the 1forge.com service as second fallback
    ->add('forge', ['api_key' => 'secret'])
     
->build();
    
// Get the latest EUR/USD rate
$rate = $swap->latest('EUR/USD');

// 1.129
$rate->getValue();

// 2016-08-26
$rate->getDate()->format('Y-m-d');

// Get the EUR/USD rate 15 days ago
$rate = $swap->historical('EUR/USD', (new \DateTime())->modify('-15 days'));
```

> We recommend to use the [services that support our project](#sponsors), providing a free plan up to 1,000 requests per day.

## Documentation

The documentation for the current branch can be found [here](https://github.com/florianv/swap/blob/3.x/doc/readme.md).

## Sponsors :heart_eyes: 

We are proudly supported by the following exchange rate providers offering *free plans up to 1,000 requests per day*:

<img src="https://s3.amazonaws.com/swap.assets/fixer_icon.png?v=2" height="20px" width="20px"/> **[Fixer](https://fixer.io)**

Fixer is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.
They provide real-time rates and historical data, however, EUR is the only available base currency on the free plan.

<img src="https://s3.amazonaws.com/swap.assets/currencylayer_icon.png" height="20px" width="20px"/> **[currencylayer](https://currencylayer.com)**

Currencylayer provides reliable exchange rates and currency conversions for your business up to 168 world currencies.
They provide real-time rates and historical data, however, USD is the only available base currency on the free plan.

<img src="https://s3.amazonaws.com/swap.assets/1forge_icon.png" height="20px" width="20px"/> **[1Forge](https://1forge.com)**

1Forge provides Forex and Cryptocurrency quotes for over 700 unique currency pairs. 
They provide the fastest price updates available of any provider, however, they don’t support smaller currencies or historical data.

## Services

Here is the list of the currently implemented services:

| Service | Base Currency | Quote Currency | Historical |
|---------------------------------------------------------------------------|----------------------|----------------|----------------|
| [Fixer](https://fixer.io) | EUR (free, no SSL), * (paid) | * | Yes |
| [currencylayer](https://currencylayer.com) | USD (free), * (paid) | * | Yes |
| [1Forge](https://1forge.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [European Central Bank](https://www.ecb.europa.eu/home/html/index.en.html) | EUR | * | Yes |
| [National Bank of Romania](http://www.bnr.ro) | RON | * | Yes |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | * | TRY | Yes |
| [Central Bank of the Czech Republic](https://www.cnb.cz) | * | CZK | Yes |
| [Central Bank of Russia](https://cbr.ru) | * | RUB | Yes |
| [WebserviceX](http://www.webservicex.net) | * | * | No |
| [Google](https://www.google.com/finance) | * | * | No |
| [Cryptonator](https://www.cryptonator.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies)  | No |
| [CurrencyDataFeed](https://currencydatafeed.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | * | * | Yes |
| [CurrencyConverterApi](https://www.currencyconverterapi.com) | * | * | Yes (free but limited or paid) |
| Array | * | * | Yes |

## Integrations

- A Symfony Bundle [FlorianvSwapBundle](https://github.com/florianv/FlorianvSwapBundle)
- A Laravel Package [florianv/laravel-swap](https://github.com/florianv/laravel-swap)

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/3.x/LICENSE) for more information.
