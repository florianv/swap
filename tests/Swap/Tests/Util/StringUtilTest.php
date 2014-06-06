<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Util;

use Swap\Util\StringUtil;

class StringUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testXmlToElement()
    {
        $element = StringUtil::xmlToElement('<root>hello</root>');

        $this->assertInstanceOf('\SimpleXMLElement', $element);
        $this->assertEquals('hello', (string) $element);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testXmlToElementInvalidString()
    {
        StringUtil::xmlToElement('/');
    }

    public function testJsonToArray()
    {
        $json = StringUtil::jsonToArray('{"license": "MIT"}');
        $this->assertEquals('MIT', $json['license']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testJsonToArrayInvalidString()
    {
        StringUtil::jsonToArray('/');
    }
}
