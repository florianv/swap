<?php

require_once __DIR__ . '/vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Swap\HistoricalExchangeQuery;
use Swap\Swap;

// Instantiate your Http Adapter
$httpAdapter = new GuzzleClient();

// Create the Yahoo Finance provider
$yahooProvider = new \Swap\Provider\EuropeanCentralBankProvider($httpAdapter);

// Create Swap with the provider
$swap = new Swap($yahooProvider);

$rate = $swap->getExchangeRate(
    //new HistoricalExchangeQuery(\Swap\Model\CurrencyPair::createFromString('EUR/USD'), (new DateTime())->modify('-15 days'))
    new \Swap\ExchangeQuery(\Swap\Model\CurrencyPair::createFromString('EUR/USD'))
);

var_dump($rate);
