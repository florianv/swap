<?php

/*
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
    /**
     * @test
     */
    public function it_converts_an_xml_string_to_element()
    {
        $element = StringUtil::xmlToElement('<root>hello</root>');

        $this->assertInstanceOf('\SimpleXMLElement', $element);
        $this->assertEquals('hello', (string) $element);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_throws_an_exception_when_converting_invalid_xml()
    {
        StringUtil::xmlToElement('/');
    }

    /**
     * @test
     */
    public function it_converts_a_json_string_to_array()
    {
        $json = StringUtil::jsonToArray('{"license": "MIT"}');
        $this->assertEquals('MIT', $json['license']);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_throws_an_exception_when_converting_invalid_json()
    {
        StringUtil::jsonToArray('/');
    }
}
