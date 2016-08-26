<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Service;

use Swap\Exception\ChainException;
use Swap\Exception\Exception;
use Swap\Exception\InternalException;
use Swap\ExchangeRate;
use Swap\ExchangeRateQuery;
use Swap\CurrencyPair;
use Swap\Service\Chain;

class ChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_does_not_support_all_queries()
    {
        // Supported
        $providerOne = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerOne
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerTwo = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerTwo
            ->expects($this->never())
            ->method('supportQuery')
            ->will($this->returnValue(false));

        $chain = new Chain([$providerOne, $providerTwo]);

        $this->assertTrue($chain->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('TRY/EUR'))));

        // Not Supported
        $providerOne = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerOne
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(false));

        $providerTwo = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerTwo
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(false));

        $chain = new Chain([$providerOne, $providerTwo]);

        $this->assertFalse($chain->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('TRY/EUR'))));
    }

    /**
     * @test
     */
    public function it_use_next_provider_in_the_chain()
    {
        $pair = new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'));
        $rate = new ExchangeRate(1, new \DateTime());

        $providerOne = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerOne
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerOne
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($pair)
            ->will($this->throwException(new Exception()));

        $providerTwo = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerTwo
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerTwo
            ->expects($this->once())
            ->method('getExchangeRate')
            ->with($pair)
            ->will($this->returnValue($rate));

        $providerThree = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerThree
            ->expects($this->never())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerThree
            ->expects($this->never())
            ->method('getExchangeRate');

        $chain = new Chain([$providerOne, $providerTwo, $providerThree]);
        $fetchedRate = $chain->getExchangeRate($pair);

        $this->assertSame($rate, $fetchedRate);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_all_providers_fail()
    {
        $exception = new Exception();
        $providerOne = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerOne
            ->expects($this->once())
            ->method('getExchangeRate')
            ->will($this->throwException($exception));

        $providerOne
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerTwo = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerTwo
            ->expects($this->once())
            ->method('getExchangeRate')
            ->will($this->throwException($exception));

        $providerTwo
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $chain = new Chain([$providerOne, $providerTwo]);
        $caught = false;

        try {
            $chain->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));
        } catch (ChainException $e) {
            $caught = true;
            $this->assertEquals([$exception, $exception], $e->getExceptions());
        }

        $this->assertTrue($caught);
    }

    /**
     * @test
     * @expectedException \Swap\Exception\InternalException
     */
    public function it_throws_an_exception_when_an_internal_exception_is_thrown()
    {
        $internalException = new InternalException();

        $providerOne = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerOne
            ->expects($this->once())
            ->method('supportQuery')
            ->will($this->returnValue(true));

        $providerOne
            ->expects($this->once())
            ->method('getExchangeRate')
            ->will($this->throwException($internalException));

        $providerTwo = $this->getMock('Swap\Contract\ExchangeRateService');

        $providerTwo
            ->expects($this->never())
            ->method('getExchangeRate');

        $chain = new Chain([$providerOne, $providerTwo]);
        $chain->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/USD')));
    }
}
