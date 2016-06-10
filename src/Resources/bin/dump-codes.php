<?php

function fetchCodes($url)
{
    $xml = new SimpleXMLElement(file_get_contents($url));
    $codes = [];

    foreach ($xml->xpath('//Ccy') as $node) {
        $codes[] = (string) $node;
    }

    return $codes;
}

$codes = array_unique(array_merge(
    fetchCodes('http://www.currency-iso.org/dam/downloads/table_a1.xml'),
    fetchCodes('http://www.currency-iso.org/dam/downloads/table_a3.xml'))
);
sort($codes, SORT_ASC);

$codesLines = '';
for ($i = 0; $i < count($codes); ++$i) {
    $codesLines .= sprintf("    const ISO_%s = '%1\$s';\n", $codes[$i]);
}

echo <<<PHP
<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Util;

/**
 * Enumeration of currency codes.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class CurrencyCodes
{
$codesLines
    private function __construct()
    {
    }
}

PHP;
