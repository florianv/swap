# Documentation

## Installation

Swap is decoupled from any library sending HTTP requests (like Guzzle), instead it uses an abstraction called [HTTPlug](http://httplug.io/) which provides the http layer used to send requests to exchange rate services. This gives you the flexibility to choose what HTTP client and PSR-7 implementation you want to use.

Read more about the benefits of this and about what different HTTP clients you may use in the [HTTPlug documentation](http://docs.php-http.org/en/latest/httplug/users.html). Below is an example using Guzzle6:

```bash
composer require florianv/swap php-http/message php-http/guzzle6-adapter
```

## Documentation

There are two ways to use Swap:

- Beginner: This document is for people who wants to get started fast with Swap

- Advanced: This tutorial is for people willing to work directly with Swap interfaces
