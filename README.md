<img src="https://github.com/florianv/swap/blob/master/doc/logo.png" width="200px" align="left"/>

> Currency exchange rates library for PHP

[![Build status](http://img.shields.io/travis/florianv/swap.svg?style=flat-square)](https://travis-ci.org/florianv/swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/florianv/swap.svg?style=flat-square)](https://scrutinizer-ci.com/g/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

**Swap** allows you to retrieve currency exchange rates from various services such as [Fixer](http://fixer.io) or [Google](https://google.com/) and optionally cache the results.
It is integrated to other libraries like [`moneyphp/money`](https://github.com/moneyphp/money) and provides
a [Symfony Bundle](https://github.com/florianv/FlorianvSwapBundle) and a [Laravel Package](https://github.com/florianv/laravel-swap).

<br />

## QuickStart

```bash
$ composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

```php
use Swap\Builder;

// Build Swap with Fixer.io
$swap = (new Builder())
    ->add('fixer', ['access_key' => 'your-access-key'])
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

## Documentation

The complete documentation can be found [here](https://github.com/florianv/swap/blob/master/doc/readme.md).

## Services

Here is the list of the currently implemented services.

| Service | Registry Alias | Base Currency | Quote Currency | Historical |
|----------------------------------------------------------------|---------------|----------------------|----------------|----------------|
| [Fixer](http://fixer.io) | `fixer` | * | * | Yes |
| [European Central Bank](http://www.ecb.europa.eu/home/html/index.en.html) | `european_central_bank` | EUR | * | Yes |
| [Google](http://www.google.com/finance) | `google` | * | * | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | `xignite` | * | * | Yes |
| [WebserviceX](http://www.webservicex.net/ws/default.aspx) | `webservicex` | * | * | No |
| [National Bank of Romania](http://www.bnr.ro) | `national_bank_of_romania` | RON | * | No |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | `central_bank_of_republic_turkey` | * | TRY | No |
| [Central Bank of the Czech Republic](http://www.cnb.cz) | `central_bank_of_czech_republic` | * | CZK | No |
| [Russian Central Bank](http://http://www.cbr.ru) | `russian_central_bank` | * | RUB | Yes |
| [currencylayer](https://currencylayer.com) | `currency_layer` | USD (free), * (paid) | * | Yes |
| [Cryptonator](https://www.cryptonator.com) | `cryptonator` | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies)  | No |
| [1Forge](https://1forge.com) | `forge` | * (free but limited or paid) | * (free but limited or paid) | No |
| [CurrencyDataFeed](https://currencydatafeed.com) | `currency_data_feed` | * (free but limited or paid) | * (free but limited or paid) | No |
| Array | `array` | * | * | Yes |

## Integrations

- A Symfony Bundle [FlorianvSwapBundle](https://github.com/florianv/FlorianvSwapBundle)
- A Laravel Package [florianv/laravel-swap](https://github.com/florianv/laravel-swap)

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/master/LICENSE) for more information.
