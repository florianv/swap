<?php

require_once __DIR__  . '/vendor/autoload.php';

// Instantiate your Http Adapter
$httpAdapter = new \Http\Adapter\Guzzle6\Client();

// Create the Yahoo Finance provider
$yahooProvider = new \Swap\Provider\CentralBankOfRepublicTurkeyProvider($httpAdapter);

// Create Swap with the provider
$swap = new \Swap\Swap($yahooProvider);

$swap->getExchangeRate(new \Swap\ExchangeQuery(\Swap\Model\CurrencyPair::createFromString('EUR/TRY')));
