<img src="doc/logo.png" width="200px" align="left"/>
> Currency exchange rates library for PHP

[![Build status](http://img.shields.io/travis/florianv/swap.svg?style=flat-square)](https://travis-ci.org/florianv/swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/florianv/swap.svg?style=flat-square)](https://scrutinizer-ci.com/g/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

Swap allows you to retrieve currency exchange rates from various services such as Fixer or Yahoo and optionally cache the results.
It is integrated to other libraries such as [`moneyphp/money`](https://github.com/moneyphp/money) and provides
a [Symfony Bundle](https://github.com/florianv/FlorianvSwapBundle) and a [Laravel Package](https://github.com/florianv/laravel-swap).

<br />

## Documentation

The documentation can be found [here](https://github.com/florianv/swap/blob/master/doc/doc.md).

## Services

Here is the list of the currently implemented services.

| Service | Base Currency | Quote Currency | Historical |
|---------------------------------------------------------------------------|----------------------|----------------|----------------|
| [Fixer](http://fixer.io) | * | * | Yes |
| [European Central Bank](http://www.ecb.europa.eu/home/html/index.en.html) | EUR | * | Yes |
| [Google Finance](http://www.google.com/finance) | * | * | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | * | * | Yes |
| [Yahoo Finance](https://finance.yahoo.com) | * | * | No |
| [WebserviceX](http://www.webservicex.net/ws/default.aspx) | * | * | No |
| [National Bank of Romania](http://www.bnr.ro) | RON | * | No |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | * | TRY | No |
| [Central Bank of the Czech Republic](http://www.cnb.cz) | * | CZK | No |
| [currencylayer](https://currencylayer.com) | USD (free), * (paid) | * | Yes |

## Integrations

- A Symfony Bundle [FlorianvSwapBundle](https://github.com/florianv/FlorianvSwapBundle)
- A Laravel Package [florianv/laravel-swap](https://github.com/florianv/laravel-swap)

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/master/LICENSE) for more information.
