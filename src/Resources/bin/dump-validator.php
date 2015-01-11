<?php

$xml = new SimpleXMLElement(file_get_contents('http://www.currency-iso.org/dam/downloads/table_a1.xml'));
$codes = [];

foreach ($xml->xpath('//Ccy') as $node) {
    $codes[] = (string) $node;
}

$codeArray = '';
$codesCount = count($codes);

for ($i = 0; $i < $codesCount; $i++) {
    if (0 === $i % 10) {
        $codeArray .= "\n" . str_repeat(' ', 10);
    }

    $codeArray .= "'" . $codes[$i] . "'";

    if ($i !== $codesCount - 1) {
        $codeArray .= ', ';
    }
}

$codeArray .= "\n" . str_repeat(' ', 4);

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
 * Utility class to validate currency codes.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
final class CurrencyCodeValidator
{
    private static \$codes = [$codeArray];

    /**
     * Checks if the code is valid.
     *
     * @param string \$code
     *
     * @return bool
     */
    public static function isValid(\$code)
    {
        return in_array(\$code, self::\$codes, true);
    }
}

PHP;
