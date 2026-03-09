<?php

declare(strict_types=1);

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests;

use Exchanger\Contract\ExchangeRateService;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Swap\Builder;
use Swap\Swap;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new Builder();
        $this->builder->useHttpClient($this->createMock(ClientInterface::class));
    }

    #[Test]
    public function buildNoServicesAdded()
    {
        $this->assertInstanceOf(Swap::class, $this->builder->build());
    }

    #[Test]
    public function buildSomeOptionsAdded()
    {
        $builder = new Builder(['cache_ttl']);
        $builder->useHttpClient($this->createMock(ClientInterface::class));

        $this->assertInstanceOf(Swap::class, $builder->build());
    }

    #[Test]
    public function buildMultipleServicesAdded()
    {
        $this->builder->add('fixer', ['access_key' => 'access_key']);
        $this->builder->add('open_exchange_rates', ['app_id' => 'secret']);
        $this->assertInstanceOf(Swap::class, $this->builder->build());
    }

    #[Test]
    public function useInvalidClient()
    {
        $this->expectException(\LogicException::class);
        $expectedExceptionMessage = 'Client must be an instance of Http\Client\HttpClient or Psr\Http\Client\ClientInterface';
        $this->expectExceptionMessage($expectedExceptionMessage);

        $builder = new Builder();
        $builder->useHttpClient(new \stdClass());
    }

    #[Test]
    public function addServiceDirectly()
    {
        $mockExchangeRateService = $this->createMock(ExchangeRateService::class);

        $this->builder->addExchangeRateService($mockExchangeRateService);

        $swap = $this->builder->build();

        $mockExchangeRateService
            ->method('supportQuery')
            ->willReturn(true);

        $rate = new ExchangeRate(
            new CurrencyPair('EUR', 'USD'),
            0.8,
            new \DateTimeImmutable(),
            'myprovider'
        );

        $mockExchangeRateService->expects($this->once())
            ->method('getExchangeRate')
            ->willReturn($rate);

        $retrievedRate = $swap->latest('EUR/USD');

        $this->assertSame($rate, $retrievedRate);
    }
}
