<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Swap\Model\CurrencyPair;
use Swap\Swap;

class SwapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    function it_adds_a_provider()
    {
        $provider = $this->getMock('Swap\ProviderInterface');

        $swap = new Swap();
        $swap->addProvider($provider);

        $this->assertTrue($swap->hasProvider($provider));
    }

    /**
     * @test
     */
    function it_can_be_constructed_with_multiple_providers()
    {
        $providerOne = $this->getMock('Swap\ProviderInterface');
        $providerTwo = $this->getMock('Swap\ProviderInterface');

        $swap = new Swap(array($providerOne, $providerTwo));

        $this->assertTrue($swap->hasProvider($providerOne));
        $this->assertTrue($swap->hasProvider($providerTwo));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_a_runtime_exception_if_no_providers()
    {
        $swap = new Swap();
        $swap->quote(new CurrencyPair('EUR', 'USD'));
    }

    /**
     * @test
     */
    function it_quotes_a_pair()
    {
        $pair = new CurrencyPair('EUR', 'USD');

        $provider = $this->getMock('Swap\ProviderInterface');
        $provider
            ->expects($this->once())
            ->method('quote')
            ->with(array($pair));

        $swap = new Swap();
        $swap->addProvider($provider);
        $swap->quote($pair);
    }

    /**
     * @test
     */
    function it_doesnt_call_providers_if_pairs_empty()
    {
        $provider = $this->getMock('Swap\ProviderInterface');
        $provider
            ->expects($this->never())
            ->method('quote');

        $swap = new Swap();
        $swap->addProvider($provider);
        $swap->quote(array());
    }

    /**
     * @test
     */
    function it_sets_the_rate_and_date_if_base_and_quote_currencies_are_identical()
    {
        $pairOne = new CurrencyPair('EUR', 'EUR');
        $pairTwo = new CurrencyPair('USD', 'USD');

        $swap = new Swap();
        $swap->addProvider($this->getMock('Swap\ProviderInterface'));
        $swap->quote(array($pairOne, $pairTwo));

        $this->assertSame('1', $pairOne->getRate());
        $this->assertNotNull($pairOne->getDate());
        $this->assertInstanceOf('\DateTime', $pairOne->getDate());
        $this->assertSame('1', $pairTwo->getRate());
        $this->assertNotNull($pairTwo->getDate());
        $this->assertInstanceOf('\DateTime', $pairTwo->getDate());
    }

    /**
     * @test
     */
    function it_forwards_failed_pair_to_next_provider()
    {
        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('USD', 'EUR');
        $pairThree = new CurrencyPair('GBP', 'USD');

        $providerOne = $this->getMock('Swap\ProviderInterface');
        $providerOne
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairOne, $pairTwo, $pairThree))
            ->will($this->returnCallback(function () use ($pairOne) {
                $pairOne->setRate('1');
            }));

        $providerTwo = $this->getMock('Swap\ProviderInterface');
        $providerTwo
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairTwo, $pairThree))
            ->will($this->returnCallback(function () use ($pairTwo) {
                $pairTwo->setRate('1');
            }));

        $providerThree = $this->getMock('Swap\ProviderInterface');
        $providerThree
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairThree))
            ->will($this->returnCallback(function () use ($pairThree) {
                $pairThree->setRate('1');
            }));

        $swap = new Swap(array($providerOne, $providerTwo, $providerThree));
        $swap->quote(array($pairOne, $pairTwo, $pairThree));

        $this->assertEquals('1', $pairOne->getRate());
        $this->assertEquals('1', $pairTwo->getRate());
        $this->assertEquals('1', $pairThree->getRate());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_the_exception_of_the_last_provider()
    {
        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('USD', 'EUR');
        $pairThree = new CurrencyPair('GBP', 'USD');

        $providerOne = $this->getMock('Swap\ProviderInterface');
        $providerOne
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairOne, $pairTwo, $pairThree))
            ->will($this->throwException(new \Exception()));

        $providerTwo = $this->getMock('Swap\ProviderInterface');
        $providerTwo
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairOne, $pairTwo, $pairThree))
            ->will($this->throwException(new \Exception()));

        $providerThree = $this->getMock('Swap\ProviderInterface');
        $providerThree
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairOne, $pairTwo, $pairThree))
            ->will($this->throwException(new \RuntimeException()));

        $swap = new Swap(array($providerOne, $providerTwo, $providerThree));
        $swap->quote(array($pairOne, $pairTwo, $pairThree));
    }

    /**
     * @test
     */
    function it_does_not_call_next_providers_if_all_pairs_are_quoted_correctly()
    {
        $pairOne = new CurrencyPair('EUR', 'USD');
        $pairTwo = new CurrencyPair('USD', 'EUR');

        $providerOne = $this->getMock('Swap\ProviderInterface');
        $providerOne
            ->expects($this->once())
            ->method('quote')
            ->with(array($pairOne, $pairTwo))
            ->will($this->returnCallback(function () use ($pairOne, $pairTwo) {
                $pairOne->setRate('1');
                $pairTwo->setRate('1');
            }));

        $providerTwo = $this->getMock('Swap\ProviderInterface');
        $providerTwo
            ->expects($this->never())
            ->method('quote');

        $swap = new Swap(array($providerOne, $providerTwo));
        $swap->quote(array($pairOne, $pairTwo));
    }
}
