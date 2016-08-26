<?php
/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Service\Traits;

/**
 * Service Trait for getting its name.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
trait GetName
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $parts = explode('\\', get_class($this));

        return strtolower(preg_replace('/\B([A-Z])/', '_$1', end($parts)));
    }
}
