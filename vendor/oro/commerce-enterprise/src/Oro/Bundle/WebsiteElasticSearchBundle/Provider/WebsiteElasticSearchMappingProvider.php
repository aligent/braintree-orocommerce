<?php

namespace Oro\Bundle\WebsiteElasticSearchBundle\Provider;

use Oro\Bundle\ElasticSearchBundle\Engine\MappingValidator;
use Oro\Bundle\WebsiteSearchBundle\Loader\ConfigurationLoaderInterface;
use Oro\Bundle\WebsiteSearchBundle\Provider\WebsiteSearchMappingProvider;

class WebsiteElasticSearchMappingProvider extends WebsiteSearchMappingProvider
{
    /** @var MappingValidator */
    private $mappingValidator;

    /**
     * @var bool
     */
    private $isMappingCorrect = false;

    /**
     * @param ConfigurationLoaderInterface $mappingConfigurationLoader
     * @param MappingValidator $mappingValidator
     */
    public function __construct(
        ConfigurationLoaderInterface $mappingConfigurationLoader,
        MappingValidator $mappingValidator
    ) {
        $this->mappingValidator = $mappingValidator;
        parent::__construct($mappingConfigurationLoader);
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingConfig()
    {
        $configuration = parent::getMappingConfig();

        if ($this->isMappingCorrect) {
            return $configuration;
        }

        $mappings = [];
        foreach ($configuration as $entity => $entityMapping) {
            foreach ($entityMapping['fields'] as $fieldData) {
                $mappings[] = [
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'],
                    'entity' => $entity,
                ];
            }
        }

        $this->getMappingValidator()->validateMappings($mappings);
        $this->isMappingCorrect = true;

        return $configuration;
    }

    /**
     * @return MappingValidator
     */
    protected function getMappingValidator()
    {
        return $this->mappingValidator;
    }
}
