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

/**
 * Helps building services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ServiceFactory
{
    /**
     * Creates a new service.
     *
     * @param string $serviceName
     * @param array  $args
     *
     * @return \Swap\Contract\ExchangeRateService
     */
    public static function createService($serviceName, array $args = [])
    {
        $services = self::getServices();

        if (!isset($services[$serviceName])) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not registered.'));
        }

        $class = $services[$serviceName];

        if (is_subclass_of($class, Service::class)) {
            return new $class(null, null, $args);
        }

        $r = new \ReflectionClass($class);

        return $r->newInstanceArgs($args);
    }

    /**
     * Gets the available services.
     *
     * @return array
     */
    private static function getServices()
    {
        return [
            'central_bank_of_czech_republic' => CentralBankOfCzechRepublic::class,
            'central_bank_of_republic_turkey' => CentralBankOfRepublicTurkey::class,
            'chain' => Chain::class,
            'currencylayer' => CurrencyLayer::class,
            'european_central_bank' => EuropeanCentralBank::class,
            'fixer' => Fixer::class,
            'google' => Google::class,
            'national_bank_of_romania' => NationalBankOfRomania::class,
            'open_exchange_rates' => OpenExchangeRates::class,
            'array' => PhpArray::class,
            'webservicex' => WebserviceX::class,
            'xignite' => Xignite::class,
            'yahoo' => Yahoo::class
        ];
    }
}
