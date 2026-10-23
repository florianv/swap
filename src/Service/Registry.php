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

namespace Swap\Service;

use Exchanger\Contract\ExchangeRateService;
use Exchanger\Service\Registry as ExchangerRegistry;

/**
 * Holds services.
 *
 * @api
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Registry
{
    /**
     * The registered services: a service class, or a factory called without arguments that returns the service.
     *
     * @var array<string, class-string<ExchangeRateService>|callable(): ExchangeRateService>
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
    public function has(string $name): bool
    {
        return isset(self::$services[$name]);
    }

    /**
     * Gets a service.
     *
     * @param string $name The service name
     *
     * @return class-string<ExchangeRateService>|callable(): ExchangeRateService|null The service class or factory, or null if not registered
     */
    public function get(string $name)
    {
        return self::$services[$name] ?? null;
    }

    /**
     * Registers a new service.
     *
     * @param string                                                         $name            The service name
     * @param class-string<ExchangeRateService>|callable(): ExchangeRateService $classOrCallable The service class, or a factory called without arguments that returns the service
     *
     * @throws \InvalidArgumentException
     */
    public static function register(string $name, $classOrCallable): void
    {
        self::$services[$name] = $classOrCallable;
    }

    /**
     * Registers the core services.
     */
    private function registerServices(): void
    {
        /** @var array<string, class-string<ExchangeRateService>> $services */
        $services = ExchangerRegistry::getServices();

        foreach ($services as $name => $class) {
            self::register($name, $class);
        }
    }
}
