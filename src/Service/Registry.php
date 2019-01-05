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

use Exchanger\Service\Registry as ExchangerRegistry;

/**
 * Holds services.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class Registry
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
    public function has(string $name): bool
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
    public function get(string $name)
    {
        return isset(self::$services[$name]) ? self::$services[$name] : null;
    }

    /**
     * Registers a new service.
     *
     * @param string          $name            The service name
     * @param string|callable $classOrCallable The class name or a callable
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
        $services = ExchangerRegistry::getServices();

        foreach ($services as $name => $class) {
            self::register($name, $class);
        }
    }
}
