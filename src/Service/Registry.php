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
     * The services registered with register(), shared by all instances: a service class, or a factory
     * called without arguments that returns the service.
     *
     * @var array<string, class-string<ExchangeRateService>|callable(): ExchangeRateService>
     */
    private static $services = [];

    /**
     * The core services of this instance.
     *
     * @var array<string, class-string<ExchangeRateService>>
     */
    private $coreServices;

    /**
     * Constructor.
     */
    public function __construct()
    {
        /** @var array<string, class-string<ExchangeRateService>> $services */
        $services = ExchangerRegistry::getServices();

        $this->coreServices = $services;
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
        return isset(self::$services[$name]) || isset($this->coreServices[$name]);
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
        return self::$services[$name] ?? $this->coreServices[$name] ?? null;
    }

    /**
     * Registers a new service.
     *
     * The service is visible to every instance, and replaces a core service of the same name.
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
}
