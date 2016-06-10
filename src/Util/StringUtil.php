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
 * Utility class to manipulate strings.
 *
 * @author GuzzleHttp
 */
final class StringUtil
{
    /**
     * Transforms an XML string to an element.
     *
     * @param string $string
     *
     * @return \SimpleXMLElement
     *
     * @throws \RuntimeException
     */
    public static function xmlToElement($string)
    {
        $disableEntities = libxml_disable_entity_loader(true);
        $internalErrors = libxml_use_internal_errors(true);

        try {
            // Allow XML to be retrieved even if there is no response body
            $xml = new \SimpleXMLElement($string ?: '<root />', LIBXML_NONET);

            libxml_disable_entity_loader($disableEntities);
            libxml_use_internal_errors($internalErrors);
        } catch (\Exception $e) {
            libxml_disable_entity_loader($disableEntities);
            libxml_use_internal_errors($internalErrors);

            throw new \RuntimeException('Unable to parse XML data: '.$e->getMessage());
        }

        return $xml;
    }

    /**
     * Transforms a JSON string to an array.
     *
     * @param string $string
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public static function jsonToArray($string)
    {
        static $jsonErrors = [
            JSON_ERROR_DEPTH => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
            JSON_ERROR_SYNTAX => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $data = json_decode($string, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();
            throw new \RuntimeException(
                'Unable to parse JSON data: '
                .(isset($jsonErrors[$last]) ? $jsonErrors[$last] : 'Unknown error')
            );
        }

        return $data;
    }
}
