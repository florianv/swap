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

use Exchanger\ExchangeRate;
use Exchanger\Service\CentralBankOfCzechRepublic;
use Exchanger\Service\CentralBankOfRepublicTurkey;
use Exchanger\Service\Cryptonator;
use Exchanger\Service\CurrencyDataFeed;
use Exchanger\Service\CurrencyLayer;
use Exchanger\Service\EuropeanCentralBank;
use Exchanger\Service\Fixer;
use Exchanger\Service\Forge;
use Exchanger\Service\Google;
use Exchanger\Service\NationalBankOfRomania;
use Exchanger\Service\OpenExchangeRates;
use Exchanger\Service\PhpArray;
use Exchanger\Service\WebserviceX;
use Exchanger\Service\Xignite;
use Exchanger\Service\Yahoo;
use Exchanger\Service\RussianCentralBank;
use Swap\Service\Factory;
use Swap\Service\Registry;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider servicesProvider
     */
    public function testCoreServices($name, $class, array $options = [])
    {
        $factory = new Factory();

        $this->assertInstanceOf($class, $factory->create($name, $options));
    }

    public function servicesProvider()
    {
        return [
            ['central_bank_of_czech_republic', CentralBankOfCzechRepublic::class],
            ['central_bank_of_republic_turkey', CentralBankOfRepublicTurkey::class],
            ['currency_data_feed', CurrencyDataFeed::class, ['api_key' => 'api_key']],
            ['currency_layer', CurrencyLayer::class, ['access_key' => 'access_key']],
            ['european_central_bank', EuropeanCentralBank::class],
            ['fixer', Fixer::class],
            ['forge', Forge::class, ['api_key' => 'api_key']],
            ['google', Google::class],
            ['national_bank_of_romania', NationalBankOfRomania::class],
            ['open_exchange_rates', OpenExchangeRates::class, ['app_id' => 'app_id']],
            ['array', PhpArray::class, [['EUR/USD' => new ExchangeRate('10')]]],
            ['webservicex', WebserviceX::class],
            ['xignite', Xignite::class, ['token' => 'token']],
            ['yahoo', Yahoo::class],
            ['russian_central_bank', RussianCentralBank::class],
            ['cryptonator', Cryptonator::class],
        ];
    }

    public function testCustomServices()
    {
        // Historical
        Registry::register('foo', OpenExchangeRates::class);

        // Default service
        Registry::register('bar', Google::class);

        // Callback
        $service = new Google();
        Registry::register('baz', function () use ($service) {
            return $service;
        });

        $factory = new Factory();

        $this->assertInstanceOf(OpenExchangeRates::class, $factory->create('foo', ['app_id' => 'app_id']));
        $this->assertInstanceOf(Google::class, $factory->create('bar'));
        $this->assertSame($service, $factory->create('baz'));
    }
}
