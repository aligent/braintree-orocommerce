<?php

namespace Oro\Bundle\MultiCurrencyBundle\Provider;

use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

class DependentConfigProvider
{
    protected $dependencyCollection = [];

    protected $failedDependencyName = '';

    /**
     * @param ConfigDependencyInterface $dependency
     */
    public function addDependency(ConfigDependencyInterface $dependency)
    {
        $this->dependencyCollection[] = $dependency;
    }

    /**
     * @param $enabledCurrencyList
     * @param OrganizationInterface $organization
     * @return bool
     */
    public function isDependenciesValid($enabledCurrencyList, OrganizationInterface $organization = null)
    {
        /** @var ConfigDependencyInterface $dependency */
        foreach ($this->dependencyCollection as $dependency) {
            if (!$dependency->isValid($enabledCurrencyList, $organization)) {
                $this->failedDependencyName = $dependency->getName();
                return false;
            }
        }

        return true;
    }

    /**
     * Returns name of failed dependency
     *
     * @return string returns dependency name or empty string if all dependencies are valid
     */
    public function getFailedDependencyName()
    {
        return $this->failedDependencyName;
    }
}
