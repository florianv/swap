<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS3x0' => true,
        '@PHP82Migration' => true, // target PHP 8.2 syntax
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
    );
