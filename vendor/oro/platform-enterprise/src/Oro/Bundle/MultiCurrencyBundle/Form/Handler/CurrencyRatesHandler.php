<?php

namespace Oro\Bundle\MultiCurrencyBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

use Oro\Bundle\ConfigBundle\Config\ConfigChangeSet;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MultiCurrencyBundle\Entity\Repository\RateRepository;
use Oro\Bundle\MultiCurrencyBundle\Form\Type\CurrencyRatesType;
use Oro\Bundle\MultiCurrencyBundle\Provider\RateProvider;

use Oro\Bundle\MultiCurrencyBundle\DependencyInjection\Configuration as MultiCurrencyConfig;

class CurrencyRatesHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var RateProvider
     */
    protected $rateProvider;

    /**
     * @param EntityManager $entityManager
     * @param RateProvider  $rateProvider
     */
    public function __construct(EntityManager $entityManager, RateProvider $rateProvider)
    {
        $this->entityManager = $entityManager;
        $this->rateProvider = $rateProvider;
    }

    /**
     * @param ConfigManager   $manager
     * @param ConfigChangeSet $changeSet
     * @param Form            $form
     */
    public function process(ConfigManager $manager, ConfigChangeSet $changeSet, Form $form)
    {
        $currencyRatesConfigKey = MultiCurrencyConfig::getConfigKeyByName(MultiCurrencyConfig::KEY_CURRENCY_RATES);
        if ($changeSet->isChanged($currencyRatesConfigKey)) {
            $submittedData = $form->get(CurrencyRatesType::CONFIG_FORM_NAME)->getData();
            $scope = $submittedData['scope'];
            $scopeId = $manager->getScopeId();
            /**
             * @var RateRepository
             */
            $rateRepository = $this->entityManager->getRepository('OroMultiCurrencyBundle:Rate');
            if (true === $submittedData['use_parent_scope_value']) {
                $rateRepository->removeRatesByScopeAndOrganization($scope, $scopeId);
            } else {
                if ($scope !== MultiCurrencyConfig::SCOPE_NAME_ORGANIZATION && $scopeId > 0) {
                    $scope = MultiCurrencyConfig::SCOPE_NAME_ORGANIZATION;
                }
                $changes = $changeSet->getChanges();
                $oldState = $changes[$currencyRatesConfigKey]['old'];
                $newState = $changes[$currencyRatesConfigKey]['new'];
                $rateRepository->applyConfigChanges(
                    $oldState,
                    $newState,
                    $scope,
                    $scopeId
                );
            }
            $this->rateProvider->clearCache();
        }
    }
}
