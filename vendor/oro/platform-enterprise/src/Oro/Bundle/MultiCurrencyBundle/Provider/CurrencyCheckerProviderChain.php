<?php

namespace Oro\Bundle\MultiCurrencyBundle\Provider;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\Provider\AbstractChainProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\CurrencyBundle\Provider\RepositoryCurrencyCheckerProviderInterface;

class CurrencyCheckerProviderChain extends AbstractChainProvider
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array             $removingCurrencies
     * @param Organization|null $organization
     *
     * @return array
     */
    public function getEntityLabelsWithMissedCurrencies(
        array $removingCurrencies,
        Organization $organization = null
    ) {
        $entityLabelsWithMissedCurrencies = [];
        $providers = $this->getProviders();
        /**
         * @var $provider RepositoryCurrencyCheckerProviderInterface
         */
        foreach ($providers as $provider) {
            if ($provider->hasRecordsWithRemovingCurrencies($removingCurrencies, $organization)) {
                $entityLabel = $provider->getEntityLabel() ? $provider->getEntityLabel() : 'N/A';
                $entityLabel = $this->translator->trans($entityLabel);
                $entityLabelsWithMissedCurrencies[] = $entityLabel;
            }
        }

        return $entityLabelsWithMissedCurrencies;
    }
}
