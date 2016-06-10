<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Provider;

use Swap\Exception\ChainProviderException;
use Swap\Exception\Exception;
use Swap\Exception\InternalException;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;
use Swap\Provider\ChainProvider;

class ChainProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_use_next_provider_in_the_chain()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $rate = new Rate(1, new \DateTime());

        $providerOne = $this->getMock('Swap\ProviderInterface');

        $providerOne
            ->expects($this->once())
            ->method('fetchRate')
            ->with($pair)
            ->will($this->throwException(new Exception()))
        ;

        $providerTwo = $this->getMock('Swap\ProviderInterface');

        $providerTwo
            ->expects($this->once())
            ->method('fetchRate')
            ->with($pair)
            ->will($this->returnValue($rate))
        ;

        $providerThree = $this->getMock('Swap\ProviderInterface');

        $providerThree
            ->expects($this->never())
            ->method('fetchRate')
        ;

        $chain = new ChainProvider([$providerOne, $providerTwo, $providerThree]);
        $fetchedRate = $chain->fetchRate($pair);

        $this->assertSame($rate, $fetchedRate);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_all_providers_fail()
    {
        $exception = new Exception();
        $providerOne = $this->getMock('Swap\ProviderInterface');

        $providerOne
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->throwException($exception))
        ;

        $providerTwo = $this->getMock('Swap\ProviderInterface');

        $providerTwo
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->throwException($exception))
        ;

        $chain = new ChainProvider([$providerOne, $providerTwo]);
        $caught = false;

        try {
            $chain->fetchRate(new CurrencyPair('EUR', 'USD'));
        } catch (ChainProviderException $e) {
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

        $providerOne = $this->getMock('Swap\ProviderInterface');
        $providerOne
            ->expects($this->once())
            ->method('fetchRate')
            ->will($this->throwException($internalException))
        ;

        $providerTwo = $this->getMock('Swap\ProviderInterface');

        $providerTwo
            ->expects($this->never())
            ->method('fetchRate')
        ;

        $chain = new ChainProvider([$providerOne, $providerTwo]);
        $chain->fetchRate(new CurrencyPair('EUR', 'USD'));
    }
}
