<?php

namespace Oro\Bundle\ElasticSearchBundle\Provider;

use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;

class ElasticSearchMappingProvider extends SearchMappingProvider
{
    /** @var MappingValidator */
    private $mappingValidator;

    /**
     * @var bool
     */
    private $isMappingCorrect = false;

    /**
     * {@inheritdoc}
     */
    public function getMappingConfig()
    {
        $configuration = parent::getMappingConfig();

        if ($this->isMappingCorrect) {
            return $configuration;
        }

        if (!$configuration || !is_array($configuration)) {
            return $configuration;
        }

        $mappings = [];
        foreach ($configuration as $entity => $entityMapping) {
            $mappings[] = $this->collectMappings($entity, $entityMapping['fields']);
        }

        if ($mappings) {
            $mappings = array_merge(...$mappings);
        }

        $this->getMappingValidator()->validateMappings($mappings);
        $this->isMappingCorrect = true;

        return $configuration;
    }

    /**
     * @param MappingValidator $mappingValidator
     */
    public function setMappingValidator(MappingValidator $mappingValidator)
    {
        $this->mappingValidator = $mappingValidator;
    }

    /**
     * @return MappingValidator
     */
    public function getMappingValidator()
    {
        if (!$this->mappingValidator) {
            throw new \RuntimeException('Mapping validator is not injected');
        }

        return $this->mappingValidator;
    }

    /**
     * @param string $entity
     * @param array $fields
     * @return array
     */
    private function collectMappings($entity, array $fields)
    {
        $mappings = [];
        $relationMappings = [];
        foreach ($fields as $fieldData) {
            if (!empty($fieldData['target_type'])) {
                $mappings[] = [
                    'name' => $fieldData['name'],
                    'type' => $fieldData['target_type'],
                    'entity' => $entity
                ];
            } elseif (!empty($fieldData['relation_fields'])) {
                // Collect relation fields mappings to separate mapping to not to do array_merge in the loop
                $relationMappings[] = $this->collectMappings($entity, $fieldData['relation_fields']);
            }
        }

        if ($relationMappings) {
            $mappings = array_merge($mappings, ...$relationMappings);
        }

        return $mappings;
    }
}
