<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Swap\Exception\UnsupportedBaseCurrencyException;
use Swap\Exception\UnsupportedCurrencyPairException;
use Guzzle\Http\ClientInterface;

/**
 * Open Exchange Rates provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class OpenExchangeRates extends AbstractMultiRequestsProvider
{
    const FREE_URI = 'https://openexchangerates.org/api/latest.json?app_id=%s';
    const ENTERPRISE_URI = 'https://openexchangerates.org/api/latest.json?app_id=%s&base=%s&symbols=%s';

    /**
     * The application id.
     *
     * @var string
     */
    private $appId;

    /**
     * A flag to tell if it is in enterprise mode.
     *
     * @var boolean
     */
    private $enterprise;

    /**
     * Creates a new provider.
     *
     * @param ClientInterface $client     The HTTP client
     * @param string          $appId      The application id.
     * @param boolean         $enterprise A flag to tell if it is in enterprise mode
     */
    public function __construct(ClientInterface $client, $appId, $enterprise = false)
    {
        parent::__construct($client);

        $this->appId = $appId;
        $this->enterprise = $enterprise;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareRequests(array $pairs)
    {
        // Build a multi dimensional array with the base currencies
        // as key and an array of quote currencies as value to generate
        // the uris for the different requests
        $orderedPairs = array();

        foreach ($pairs as $pair) {
            if (!$this->enterprise && 'USD' !== $pair->getBaseCurrency()) {
                throw new UnsupportedBaseCurrencyException($pair->getBaseCurrency());
            }

            if (empty($orderedPairs[$pair->getBaseCurrency()])) {
                $orderedPairs[$pair->getBaseCurrency()] = array($pair->getQuoteCurrency());
            } else {
                $orderedPairs[$pair->getBaseCurrency()][] = $pair->getQuoteCurrency();
            }
        }

        // Prepare the requests
        $requests = array();

        foreach ($orderedPairs as $baseCurrency => $orderedPair) {
            $symbols = array();

            foreach ($orderedPair as $quoteCurrency) {
                $symbols[] = $quoteCurrency;
            }

            if ($this->enterprise) {
                $uri = sprintf(self::ENTERPRISE_URI, $this->appId, $baseCurrency, implode(',', $symbols));
            } else {
                $uri = sprintf(self::FREE_URI, $this->appId);
            }

            $requests[] = $this->client->get($uri);
        }

        return $requests;
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponses(array $responses, array $pairs)
    {
        $pairsToProcess = $pairs;

        foreach ($responses as $response) {
            $json = $response->json();
            $base = $json['base'];
            $date = new \DateTime();
            $date->setTimestamp($json['timestamp']);

            $newPairsToProcess = array();

            foreach ($pairsToProcess as $pair) {
                if ($base === $pair->getBaseCurrency() && isset($json['rates'][$pair->getQuoteCurrency()])) {
                    $pair->setRate($json['rates'][$pair->getQuoteCurrency()]);
                    $pair->setDate($date);
                } else {
                    $newPairsToProcess[] = $pair;
                }
            }

            $pairsToProcess = $newPairsToProcess;
        }

        // Remaining pairs were not quoted, so they are not supported
        if (!empty($pairsToProcess)) {
            throw new UnsupportedCurrencyPairException($pairsToProcess[0]);
        }
    }
}
