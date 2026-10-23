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
use Exchanger\Exception\ChainException;
use Exchanger\ExchangeRate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Swap\Builder;
use Swap\Service\Registry;
use Swap\Swap;
use Swap\Tests\Fixtures\NamedService;

final class BuilderTest extends TestCase
{
    use ResetsRegistry;

    private Builder $builder;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        Registry::register('named', NamedService::class);

        $this->builder = new Builder();
        $this->builder->useHttpClient($this->createStub(ClientInterface::class));
    }

    #[Test]
    public function buildNoServicesAdded(): void
    {
        $this->assertInstanceOf(Swap::class, $this->builder->build());
    }

    #[Test]
    public function buildSomeOptionsAdded(): void
    {
        $builder = new Builder(['cache_ttl']);
        $builder->useHttpClient($this->createStub(ClientInterface::class));

        $this->assertInstanceOf(Swap::class, $builder->build());
    }

    #[Test]
    public function buildMultipleServicesAdded(): void
    {
        $this->builder->add('fixer', ['access_key' => 'access_key']);
        $this->builder->add('open_exchange_rates', ['app_id' => 'secret']);
        $this->assertInstanceOf(Swap::class, $this->builder->build());
    }

    #[Test]
    public function useInvalidClient(): void
    {
        $this->expectException(\LogicException::class);
        $expectedExceptionMessage = 'Client must be an instance of Http\Client\HttpClient or Psr\Http\Client\ClientInterface';
        $this->expectExceptionMessage($expectedExceptionMessage);

        $builder = new Builder();
        /** @psalm-suppress InvalidArgument The runtime type check is under test. */
        $builder->useHttpClient(new \stdClass());
    }

    #[Test]
    public function addServiceDirectly(): void
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

    #[Test]
    public function addingTheSameServiceNameAgainReplacesItsOptions(): void
    {
        $this->builder->add('named', ['name' => 'a', 'fail' => true]);
        $this->builder->add('named', ['name' => 'b', 'fail' => true]);

        try {
            $this->builder->build()->latest('EUR/USD');
            $this->fail('Expected a ChainException.');
        } catch (ChainException $e) {
            $messages = array_map(static fn(\Throwable $e): string => $e->getMessage(), $e->getExceptions());

            $this->assertSame(['Service "b" failed.'], $messages);
        }
    }

    #[Test]
    public function addingTheSameServiceNameAgainKeepsItsPosition(): void
    {
        $this->builder->add('named', ['name' => 'a']);
        $this->builder->addExchangeRateService(new NamedService('direct'));
        $this->builder->add('named', ['name' => 'b']);

        $this->assertSame('b', $this->builder->build()->latest('EUR/USD')->getProviderName());
    }

    #[Test]
    public function severalInstancesOfTheSameServiceCanBeAddedDirectly(): void
    {
        $this->builder->addExchangeRateService(new NamedService('a', true));
        $this->builder->addExchangeRateService(new NamedService('b'));

        $this->assertSame('b', $this->builder->build()->latest('EUR/USD')->getProviderName());
    }

    #[Test]
    public function differentServicesKeepCallOrder(): void
    {
        $this->builder->addExchangeRateService(new NamedService('direct'));
        $this->builder->add('named', ['name' => 'by-name']);

        $this->assertSame('direct', $this->builder->build()->latest('EUR/USD')->getProviderName());
    }

    #[Test]
    public function addingTheSameInstanceTwiceKeepsOneEntry(): void
    {
        $failing = new NamedService('failing', true);
        $this->builder->addExchangeRateService($failing);
        $this->builder->addExchangeRateService($failing);

        try {
            $this->builder->build()->latest('EUR/USD');
            $this->fail('Expected a ChainException.');
        } catch (ChainException $e) {
            $this->assertCount(1, $e->getExceptions());
        }
    }
}
