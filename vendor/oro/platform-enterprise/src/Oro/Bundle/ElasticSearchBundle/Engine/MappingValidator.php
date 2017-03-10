<?php

namespace Oro\Bundle\ElasticSearchBundle\Engine;

class MappingValidator
{
    /**
     * Validate ElasticSearch mappings
     * $mappings structure:
     * [
     *      [
     *          'name' => '<fieldName>'
     *          'type' => '<fieldType>',
     *          'entity' => '<entityName>'
     *      ]
     * ]
     *
     * @param array $mappings
     * @throws \LogicException in case of incorrect mapping
     */
    public function validateMappings(array $mappings)
    {
        $handledFields = [];
        foreach ($mappings as $fieldData) {
            $fieldName = $fieldData['name'];
            $fieldType = $fieldData['type'];
            $fieldEntity = $fieldData['entity'];

            if (!isset($handledFields[$fieldName])) {
                $handledFields[$fieldName]['entity'] = $fieldEntity;
                $handledFields[$fieldName]['type'] = $fieldType;

                continue;
            }

            if ($handledFields[$fieldName]['type'] !== $fieldType) {
                throw new \LogicException(
                    sprintf(
                        'ElasticSearch does not allow different types for the same field name.' . PHP_EOL .
                        'Field "%s" in entity "%s" has type "%s", but same field in entity "%s" has type "%s"',
                        $fieldName,
                        $handledFields[$fieldName]['entity'],
                        $handledFields[$fieldName]['type'],
                        $fieldEntity,
                        $fieldType
                    )
                );
            }
        }
    }
}
