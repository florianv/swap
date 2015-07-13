<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Exception;

/**
 * For internal exceptions only that are not caught by the ChainProvider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class InternalException extends Exception
{
}
