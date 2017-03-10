<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ArrayToJsonTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function transform($value)
    {
        if (null === $value || [] === $value) {
            return '[]';
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf(
                'Expected argument of type "array", "%s" given',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return json_encode($value);
    }

    /**
     * @param string $string
     *
     * @throws TransformationFailedException
     *
     * @return array
     */
    public function reverseTransform($string)
    {
        if (!is_string($string)) {
            throw new TransformationFailedException(sprintf(
                'Accept only string argument but got: "%s"',
                is_object($string) ? get_class($string) : gettype($string)
            ));
        }

        if (empty($string)) {
            return [];
        }

        $decoded = json_decode($string, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransformationFailedException(sprintf(
                'The malformed json given. Error %s and message %s',
                json_last_error(),
                json_last_error_msg()
            ));
        }

        return $decoded;
    }
}
