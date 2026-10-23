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

namespace Swap\Tests\Service;

use Exchanger\Service\CentralBankOfCzechRepublic;
use Exchanger\Service\CentralBankOfRepublicTurkey;
use Exchanger\Service\Cryptonator;
use Exchanger\Service\CurrencyDataFeed;
use Exchanger\Service\CurrencyLayer;
use Exchanger\Service\EuropeanCentralBank;
use Exchanger\Service\Fixer;
use Exchanger\Service\Forge;
use Exchanger\Service\NationalBankOfRomania;
use Exchanger\Service\OpenExchangeRates;
use Exchanger\Service\PhpArray;
use Exchanger\Service\WebserviceX;
use Exchanger\Service\Xignite;
use Exchanger\Service\RussianCentralBank;
use Exchanger\Service\XchangeApi;
use Http\Mock\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Swap\Service\Factory;
use Swap\Service\Registry;

final class FactoryTest extends TestCase
{
    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('servicesProvider')]
    public function coreServices(string $name, string $class, array $options = []): void
    {
        $factory = new Factory(new Client());

        $this->assertInstanceOf($class, $factory->create($name, $options));
    }

    /**
     * @return list<array{0: string, 1: class-string, 2?: array}>
     */
    public static function servicesProvider(): array
    {
        return [
            ['central_bank_of_czech_republic', CentralBankOfCzechRepublic::class],
            ['central_bank_of_republic_turkey', CentralBankOfRepublicTurkey::class],
            ['currency_data_feed', CurrencyDataFeed::class, ['api_key' => 'api_key']],
            ['currency_layer', CurrencyLayer::class, ['access_key' => 'access_key']],
            ['european_central_bank', EuropeanCentralBank::class],
            ['fixer', Fixer::class, ['access_key' => 'access_key']],
            ['forge', Forge::class, ['api_key' => 'api_key']],
            ['national_bank_of_romania', NationalBankOfRomania::class],
            ['open_exchange_rates', OpenExchangeRates::class, ['app_id' => 'app_id']],
            ['array', PhpArray::class, [['EUR/USD' => 1.0]]],
            ['webservicex', WebserviceX::class],
            ['xignite', Xignite::class, ['token' => 'token']],
            ['russian_central_bank', RussianCentralBank::class],
            ['cryptonator', Cryptonator::class],
            ['xchangeapi', XchangeApi::class, ['api-key' => 'api-key']],
        ];
    }

    #[Test]
    public function customServices(): void
    {
        // Historical
        Registry::register('foo', OpenExchangeRates::class);

        // Default service
        Registry::register('bar', EuropeanCentralBank::class);

        // Callback
        $service = new PhpArray([], []);
        Registry::register('baz', function () use ($service): PhpArray {
            return $service;
        });

        $factory = new Factory(new Client());

        $this->assertInstanceOf(OpenExchangeRates::class, $factory->create('foo', ['app_id' => 'app_id']));
        $this->assertInstanceOf(EuropeanCentralBank::class, $factory->create('bar'));
        $this->assertSame($service, $factory->create('baz'));
    }

    #[Test]
    public function constructInvalidClient(): void
    {
        $this->expectException(\LogicException::class);
        $expectedExceptionMessage = 'Client must be an instance of Http\Client\HttpClient or Psr\Http\Client\ClientInterface';
        $this->expectExceptionMessage($expectedExceptionMessage);

        /** @psalm-suppress InvalidArgument The runtime type check is under test. */
        new Factory(new \stdClass());
    }

    #[Test]
    public function withNullAsClientUsesDiscovery(): void
    {
        // php-http/mock-client (dev dep) provides client-implementation,
        // so PSR-18 discovery succeeds and Factory can create services.
        $factory = new Factory();

        $this->assertInstanceOf(EuropeanCentralBank::class, $factory->create('european_central_bank'));
    }

    #[Test]
    public function setInvalidClient(): void
    {
        $this->expectException(\LogicException::class);
        $expectedExceptionMessage = 'Client must be an instance of Http\Client\HttpClient or Psr\Http\Client\ClientInterface';
        $this->expectExceptionMessage($expectedExceptionMessage);

        $factory = new Factory(new Client());
        /** @psalm-suppress InvalidArgument The runtime type check is under test. */
        $factory->setHttpClient(new \stdClass());
    }
}
