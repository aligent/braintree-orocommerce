<?php

namespace Oro\Bundle\MultiCurrencyBundle\Tests\Unit\Transformer;

use Oro\Bundle\MultiCurrencyBundle\Form\Transformer\ArrayToJsonTransformer;

class ArrayToJsonTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayToJsonTransformer
     */
    protected $arrayToJsonTransformer;

    public function setUp()
    {
        $this->arrayToJsonTransformer = new ArrayToJsonTransformer();
    }

    /**
     * @dataProvider transformProvider
     *
     * @param $value
     * @param $result
     * @param $exception
     * @param $givenType
     */
    public function testTransform($value, $result, $exception, $givenType)
    {
        if ($exception) {
            $this->expectException('Symfony\Component\Form\Exception\TransformationFailedException');
            $this->expectExceptionMessage(
                sprintf('Expected argument of type "array", "%s" given', $givenType)
            );
        }

        $this->assertEquals($this->arrayToJsonTransformer->transform($value), $result);
    }

    /**
     * @dataProvider reverseTransformProvider
     *
     * @param $string
     * @param $result
     * @param $exception
     * @param $message
     */
    public function testReverseTransform($string, $result, $exception, $message)
    {
        if ($exception) {
            $this->expectException('Symfony\Component\Form\Exception\TransformationFailedException');
            $this->expectExceptionMessage($message);
        }

        $this->assertEquals($this->arrayToJsonTransformer->reverseTransform($string), $result);
    }

    public function transformProvider()
    {
        return [
            'Null value' => [
                'value'     => null,
                'result'    => '[]',
                'exception' => false,
                'givent_type' => false
            ],
            'Empty array value' => [
                'value'     => [],
                'result'    => '[]',
                'exception' => false,
                'givent_type' => false
            ],
            'Array value' => [
                'value'     => ['EUR', 'USD'],
                'result'    => '["EUR","USD"]',
                'exception' => false,
                'givent_type' => false
            ],
            'String value' => [
                'value'     => 'some string',
                'result'    => false,
                'exception' => true,
                'givent_type' => 'string'
            ],
            'Int value' => [
                'value'     => 12,
                'result'    => false,
                'exception' => true,
                'givent_type' => 'integer'
            ],
            'False value' => [
                'value'     => false,
                'result'    => false,
                'exception' => true,
                'givent_type' => 'boolean'
            ],
            'Object value' => [
                'value'     => new \stdClass(),
                'result'    => false,
                'exception' => true,
                'givent_type' => 'stdClass'
            ],
        ];
    }

    public function reverseTransformProvider()
    {
        return [
            'Null' => [
                'string'     => null,
                'result'    => false,
                'exception' => true,
                'message' => 'Accept only string argument but got: "NULL"'
            ],
            'False' => [
                'string'     => false,
                'result'    => false,
                'exception' => true,
                'message' => 'Accept only string argument but got: "boolean"'
            ],
            'Empty string' => [
                'string'     => '',
                'result'    => [],
                'exception' => false,
                'message' => false
            ],
            'Invalid json' => [
                'string'     => 'invalid json',
                'result'    => false,
                'exception' => true,
                'message' => 'The malformed json given. Error 4 and message Syntax error'
            ],
            'JSON with flat array' => [
                'string'     => '["EUR","USD"]',
                'result'    => ['EUR', 'USD'],
                'exception' => false,
                'message' => false
            ],
            'JSON wit object value' => [
                'string'     => '{"USD":{"rateFrom":1,"rateTo":1}}',
                'result'    => ['USD' => [ 'rateFrom' => 1, 'rateTo' => 1 ]],
                'exception' => false,
                'message' => false
            ],
        ];
    }
}
