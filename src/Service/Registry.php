<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service;

use Exchanger\Service\CentralBankOfCzechRepublic;
use Exchanger\Service\CentralBankOfRepublicTurkey;
use Exchanger\Service\CurrencyLayer;
use Exchanger\Service\EuropeanCentralBank;
use Exchanger\Service\Fixer;
use Exchanger\Service\Google;
use Exchanger\Service\NationalBankOfRomania;
use Exchanger\Service\OpenExchangeRates;
use Exchanger\Service\PhpArray;
use Exchanger\Service\WebserviceX;
use Exchanger\Service\Xignite;
use Exchanger\Service\Yahoo;

/**
 * Holds services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Registry
{
    /**
     * The registered services.
     *
     * @var array
     */
    private static $services = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registerServices();
    }

    /**
     * Tells of the registry has the given service.
     *
     * @param string $name The service name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset(self::$services[$name]);
    }

    /**
     * Gets a service.
     *
     * @param string $name The service name
     *
     * @return string|null
     */
    public function get($name)
    {
        return isset(self::$services[$name]) ? self::$services[$name] : null;
    }

    /**
     * Returns all registered services
     *
     * @return array
     */
    public function all()
    {
        return self::$services;
    }

    /**
     * Registers a new service.
     *
     * @param string          $name            The service name
     * @param string|callable $classOrCallable The class name or a callable
     *
     * @throws \InvalidArgumentException
     */
    public static function register($name, $classOrCallable)
    {
        self::$services[$name] = $classOrCallable;
    }

    /**
     * Registers the core services.
     */
    private function registerServices()
    {
        $services = [
            'central_bank_of_czech_republic' => CentralBankOfCzechRepublic::class,
            'central_bank_of_republic_turkey' => CentralBankOfRepublicTurkey::class,
            'currency_layer' => CurrencyLayer::class,
            'european_central_bank' => EuropeanCentralBank::class,
            'fixer' => Fixer::class,
            'google' => Google::class,
            'national_bank_of_romania' => NationalBankOfRomania::class,
            'open_exchange_rates' => OpenExchangeRates::class,
            'array' => PhpArray::class,
            'webservicex' => WebserviceX::class,
            'xignite' => Xignite::class,
            'yahoo' => Yahoo::class,
        ];

        foreach ($services as $name => $class) {
            self::register($name, $class);
        }
    }
}
