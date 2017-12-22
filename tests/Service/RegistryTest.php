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
use Swap\Service\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider serviceProviders
     */
    public function testCoreServices($name, $class)
    {
        $registry = new Registry();

        $this->assertTrue($registry->has($name));
        $this->assertEquals($class, $registry->get($name));
    }

    public function serviceProviders()
    {
        return [
            ['central_bank_of_czech_republic', CentralBankOfCzechRepublic::class],
            ['central_bank_of_republic_turkey', CentralBankOfRepublicTurkey::class],
            ['currency_data_feed', CurrencyDataFeed::class],
            ['currency_layer', CurrencyLayer::class],
            ['european_central_bank', EuropeanCentralBank::class],
            ['fixer', Fixer::class],
            ['forge', Forge::class],
            ['google', Google::class],
            ['national_bank_of_romania', NationalBankOfRomania::class],
            ['open_exchange_rates', OpenExchangeRates::class],
            ['array', PhpArray::class],
            ['webservicex', WebserviceX::class],
            ['xignite', Xignite::class],
            ['yahoo', Yahoo::class],
            ['russian_central_bank', RussianCentralBank::class],
            ['cryptonator', Cryptonator::class],
        ];
    }

    public function testRegister()
    {
        $registry = new Registry();

        $registry->register('foo', 'Foo');
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals('Foo', $registry->get('foo'));

        $callable = function () {
        };
        $registry->register('foo', $callable);
        $this->assertTrue($registry->has('foo'));
        $this->assertEquals($callable, $registry->get('foo'));
    }
}
