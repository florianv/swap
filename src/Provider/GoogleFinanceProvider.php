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

use Swap\Exception\Exception;
use Swap\ExchangeQueryInterface;
use Swap\HistoricalExchangeQueryInterface;
use Swap\Model\Rate;

/**
 * Google Finance provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class GoogleFinanceProvider extends AbstractProvider
{
    const URL = 'http://www.google.com/finance/converter?a=1&from=%s&to=%s';

    /**
     * {@inheritdoc}
     */
    public function fetchRate(ExchangeQueryInterface $exchangeQuery)
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
        $url = sprintf(self::URL, $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
        $content = $this->fetchContent($url);

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        $document = new \DOMDocument();

        if (false === @$document->loadHTML($content)) {
            throw new Exception('The page content is not loadable');
        }

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//span[@class="bld"]');

        if (0 === $nodes->length) {
            throw new Exception('The currency is not supported or Google changed the response format');
        }

        $nodeContent = $nodes->item(0)->textContent;
        $bid = strstr($nodeContent, ' ', true);

        if (!is_numeric($bid)) {
            throw new Exception('The currency is not supported or Google changed the response format');
        }

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        return new Rate($bid, new \DateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function support(ExchangeQueryInterface $exchangeQuery)
    {
        return !$exchangeQuery instanceof HistoricalExchangeQueryInterface;
    }
}
