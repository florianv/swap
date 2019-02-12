# Release Notes

## 4.0

- Migrated from PHP 5.5 to PHP 7.1+
- Updated PHP HTTP dependency to version 2
- Removed the deprecated Yahoo service which has been removed by Yahoo
- Removed the deprecated Google service which has been very unreliable and unstable on different platforms
- Fixed the exchange rate value to always be a float instead of a string
- Added information about which service returned a rate with `ExchangeRate::getProviderName()`
- Removed the `InternalException` only used in the `PhpArray` service
- Modified the PHPArray service to only support scalars as rates (rate objects are not compatible)
- We now rely on PSR-16 Simple Cache instead of PSR-6 Cache. You can use https://github.com/php-cache/simple-cache-bridge
as a bridge between PSR-6 and PSR-16.
- Added a `getCurrencyPair()` to the exchange rate objects
- Removed the Historical service class in favor of the `SupportsHistoricalQueries` trait

## 3.0

This log contains the important changes for the 3.x versions.

- Added CurrencyConverterApi service
- Fixed Fixer service which now requires an access_key
- Added Forge service
- Added CurrencyDataFeed service
- Supported historical rates in National Bank Of Romania
- Support crypto currencies via Cryptonator
- New Russian Central Bank service

- e5234ea Added a .gitattributes file
- 24ba8f3 Use Httplug instead of egeloen
- 638255b PSR Cache implementation
- 80abb02 Add documentation about caching
- acd8eb6 Removed currency codes enumeration
- b0a8aba Added interfaces for CurrencyPair and Rate
- eefb252 Removed unused InvalidCurrencyCodeException
- 457a72a New Swap
- 02f0b66 Updated the documentation
- 55ab175 Rely on Exchanger 0.1
- 92a7d2c Updated README
- feb6102 Added a CHANGELOG file
