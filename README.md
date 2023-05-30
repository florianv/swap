# <img src="https://s3.amazonaws.com/swap.assets/swap_logo.png" height="30px" width="30px"/> Swap

[![Build status](http://img.shields.io/travis/florianv/swap.svg?style=flat-square)](https://travis-ci.org/florianv/swap)
[![Total Downloads](https://img.shields.io/packagist/dt/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/florianv/swap.svg?style=flat-square)](https://scrutinizer-ci.com/g/florianv/swap)
[![Version](http://img.shields.io/packagist/v/florianv/swap.svg?style=flat-square)](https://packagist.org/packages/florianv/swap)

Swap allows you to retrieve currency exchange rates from various services such as **[Fixer](https://fixer.io/)**, **[Currency Data](https://currencylayer.com)**,
**[Exchange Rates Data](https://exchangeratesapi.io/)** or **[Abstract](https://www.abstractapi.com)** and optionally cache the results. 
It is integrated to other libraries like [moneyphp/money](https://github.com/moneyphp/money) and provides
a [Symfony Bundle](https://github.com/florianv/FlorianvSwapBundle) and a [Laravel Package](https://github.com/florianv/laravel-swap).

## Sponsors

<table>
   <tr>
      <td><img src="https://assets.apilayer.com/apis/fixer.png" width="50px"/></td>
      <td><a href="https://fixer.io/">Fixer</a> is a simple and lightweight API for foreign exchange rates that supports up to 170 world currencies.</td>
   </tr>
   <tr>
     <td><img src="https://assets.apilayer.com/apis/currency_data.png" width="50px"/></td>
     <td><a href="https://currencylayer.com">currencylayer</a> provides reliable exchange rates and currency conversions for your business up to 168 world currencies.</td>
   </tr>
   <tr>
     <td><img src="https://assets.apilayer.com/apis/exchangerates_data.png" width="50px"/></td>
     <td><a href="https://exchangeratesapi.io/">exchangerates</a> provides reliable exchange rates and currency conversions for your business with over 15 data sources.</td>
   </tr>   
   <tr>
     <td><img src="https://global-uploads.webflow.com/5ebbd0a566a3996636e55959/5ec2ba29feeeb05d69160e7b_webclip.png" width="50px"/></td>
     <td><a href="https://www.abstractapi.com/">Abstract</a> provides simple exchange rates for developers and a dozen of APIs covering thousands of use cases.</td>
   </tr>  
</table>

## QuickStart

```bash
$ composer require php-http/curl-client nyholm/psr7 php-http/message florianv/swap
```

```php
use Swap\Builder;

// Build Swap
$swap = (new Builder())

    // Use the Fixer service as first level provider
    ->add('apilayer_fixer', ['api_key' => 'Get your key here: https://fixer.io/'])
     
    // Use the currencylayer service as first fallback
    ->add('apilayer_currency_data', ['api_key' => 'Get your key here: https://currencylayer.com'])
    
    // Use the exchangerates service as second fallback
    ->add('apilayer_exchange_rates_data', ['api_key' => 'Get your key here: https://exchangeratesapi.io/'])
     
    // Use the Abstract Api service as third fallback
    ->add('abstract_api', ['api_key' => 'Get your key here: https://app.abstractapi.com/users/signup'])
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

> We recommend to use the [services that support our project](#sponsors), providing a free plan up to 100 requests per month.

## Documentation

The documentation for the current branch can be found [here](https://github.com/florianv/swap/blob/master/doc/readme.md).

## Services

Here is the list of the currently implemented services:

| Service | Base Currency | Quote Currency | Historical |
|---------------------------------------------------------------------------|----------------------|----------------|----------------|
| [Fixer](https://fixer.io/) | EUR (free, no SSL), * (paid) | * | Yes |
| [Currency Data](https://currencylayer.com) | USD (free), * (paid) | * | Yes |
| [Exchange Rates Data](https://exchangeratesapi.io/) | USD (free), * (paid) | * | Yes |
| [Abstract](https://www.abstractapi.com) | * | * | Yes |
| [coinlayer](https://coinlayer.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies) | Yes |
| [Fixer](https://fixer.io) | EUR (free, no SSL), * (paid) | * | Yes |
| [Currency Data](https://currencylayer.com) | USD (free), * (paid) | * | Yes |
| [exchangeratesapi](https://exchangeratesapi.io) | USD (free), * (paid) | * | Yes |
| [European Central Bank](https://www.ecb.europa.eu/home/html/index.en.html) | EUR | * | Yes |
| [National Bank of Georgia](https://nbg.gov.ge) | * | GEL | Yes |
| [National Bank of the Republic of Belarus](https://www.nbrb.by) | * | BYN (from 01-07-2016),<br>BYR (01-01-2000 - 30-06-2016),<br>BYB (25-05-1992 - 31-12-1999) | Yes |
| [National Bank of Romania](http://www.bnr.ro) | RON, AED, AUD, BGN, BRL, CAD, CHF, CNY, CZK, DKK, EGP, EUR, GBP, HRK, HUF, INR, JPY, KRW, MDL, MXN, NOK, NZD, PLN, RSD, RUB, SEK, TRY, UAH, USD, XAU, XDR, ZAR | RON, AED, AUD, BGN, BRL, CAD, CHF, CNY, CZK, DKK, EGP, EUR, GBP, HRK, HUF, INR, JPY, KRW, MDL, MXN, NOK, NZD, PLN, RSD, RUB, SEK, TRY, UAH, USD, XAU, XDR, ZAR | Yes |
| [National Bank of Ukranie](https://bank.gov.ua) | * | UAH | Yes |
| [Central Bank of the Republic of Turkey](http://www.tcmb.gov.tr) | * | TRY | Yes |
| [Central Bank of the Republic of Uzbekistan](https://cbu.uz) | * | UZS | Yes |
| [Central Bank of the Czech Republic](https://www.cnb.cz) | * | CZK | Yes |
| [Central Bank of Russia](https://cbr.ru) | * | RUB | Yes |
| [Bulgarian National Bank](http://bnb.bg) | * | BGN | Yes |
| [WebserviceX](http://www.webservicex.net) | * | * | No |
| [1Forge](https://1forge.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Cryptonator](https://www.cryptonator.com) | * Crypto (Limited standard currencies) | * Crypto (Limited standard currencies)  | No |
| [CurrencyDataFeed](https://currencydatafeed.com) | * (free but limited or paid) | * (free but limited or paid) | No |
| [Open Exchange Rates](https://openexchangerates.org) | USD (free), * (paid) | * | Yes |
| [Xignite](https://www.xignite.com) | * | * | Yes |
| [Currency Converter API](https://www.currencyconverterapi.com) | * | * | Yes (free but limited or paid) |
| [xChangeApi.com](https://xchangeapi.com) | * | * | Yes |
| [fastFOREX.io](https://www.fastforex.io) | USD (free), * (paid) | * | No |
| [exchangerate.host](https://www.exchangerate.host) | * | * | Yes |
| Array | * | * | Yes |

Additionally, you can add your own services as long as they implement the `ExchangeRateService` interface.

## Integrations

- A Symfony Bundle [FlorianvSwapBundle](https://github.com/florianv/FlorianvSwapBundle)
- A Laravel Package [florianv/laravel-swap](https://github.com/florianv/laravel-swap)

## Credits

- [Florian Voutzinos](https://github.com/florianv)
- [All Contributors](https://github.com/florianv/swap/contributors)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/florianv/swap/blob/master/LICENSE) for more information.
