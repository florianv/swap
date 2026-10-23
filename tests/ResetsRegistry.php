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

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Swap\Service\Registry;

/**
 * Registry::register() is global by design, so tests that register services start and end with an empty registry.
 */
trait ResetsRegistry
{
    /**
     * @psalm-suppress PossiblyUnusedMethod PHPUnit calls it through the #[Before] and #[After] attributes.
     */
    #[Before]
    #[After]
    protected function resetRegistry(): void
    {
        (new \ReflectionProperty(Registry::class, 'services'))->setValue(null, []);
    }
}
