<?php

namespace Oro\Bundle\MultiWebsiteBundle\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\MultiWebsiteBundle\Form\Extension\PriceListFormExtension;
use Oro\Bundle\PricingBundle\Entity\PriceListToWebsite;
use Oro\Bundle\PricingBundle\Entity\PriceListWebsiteFallback;
use Oro\Bundle\PricingBundle\Form\PriceListWithPriorityCollectionHandler;
use Oro\Bundle\PricingBundle\Form\Type\PriceListCollectionType;
use Oro\Bundle\PricingBundle\Model\PriceListRelationTriggerHandler;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Component\Form\FormEvent;

class PriceListListener
{
    /**
     * @var PriceListWithPriorityCollectionHandler
     */
    protected $priceListCollectionHandler;

    /**
     * @var string
     */
    protected $priceListToWebsiteClass = 'Oro\Bundle\PricingBundle\Entity\PriceListToWebsite';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var PriceListRelationTriggerHandler
     */
    protected $changeTriggerHandler;

    /**
     * @var array|PriceListToWebsite[]
     */
    protected $existingRelations = [];

    /**
     * @param PriceListWithPriorityCollectionHandler $priceListCollectionHandler
     * @param DoctrineHelper $doctrineHelper
     * @param PriceListRelationTriggerHandler $changeTriggerHandler
     */
    public function __construct(
        PriceListWithPriorityCollectionHandler $priceListCollectionHandler,
        DoctrineHelper $doctrineHelper,
        PriceListRelationTriggerHandler $changeTriggerHandler
    ) {
        $this->priceListCollectionHandler = $priceListCollectionHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->changeTriggerHandler = $changeTriggerHandler;
    }

    /**
     * @param AfterFormProcessEvent $event
     */
    public function beforeFlush(AfterFormProcessEvent $event)
    {
        /** @var Website $website */
        $website = $event->getData();
        $submittedPriceLists = (array)$event->getForm()
            ->get(PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD)
            ->getData();

        $existing = $this->getExistingRelations($website);

        $hasChanges = $this->priceListCollectionHandler
            ->handleChanges($submittedPriceLists, $existing, $website, $website);

        $fallback = $this->getFallback($website);
        $submittedFallback = $event->getForm()->get(PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD)->getData();
        if (!$fallback && $submittedFallback !== PriceListWebsiteFallback::CONFIG) {
            $fallback = new PriceListWebsiteFallback();
            $this->doctrineHelper->getEntityManager($fallback)->persist($fallback);
            $hasChanges = true;
        }
        if ($fallback && $fallback->getFallback() !== $submittedFallback) {
            $fallback->setWebsite($website);
            $fallback->setFallback($submittedFallback);

            $hasChanges = true;
        }
        if ($hasChanges) {
            $this->changeTriggerHandler->handleWebsiteChange($website);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        /** @var Website|null $product */
        $website = $event->getData();

        if (!$website || !$website->getId()) {
            return;
        }

        $data = $this->getExistingRelations($website);
        $event->getForm()->get(PriceListFormExtension::PRICE_LISTS_TO_WEBSITE_FIELD)->setData($data);
        $fallback = $this->getFallback($website);
        $fallbackField = $event->getForm()->get(PriceListFormExtension::PRICE_LISTS_FALLBACK_FIELD);
        if (!$fallback || $fallback->getFallback() === PriceListWebsiteFallback::CONFIG) {
            $fallbackField->setData(PriceListWebsiteFallback::CONFIG);
        } else {
            $fallbackField->setData(PriceListWebsiteFallback::CURRENT_WEBSITE_ONLY);
        }
    }

    /**
     * @param Website $website
     * @return array|\Oro\Bundle\PricingBundle\Entity\PriceListToWebsite[]
     */
    protected function getExistingRelations(Website $website)
    {
        if (!$this->existingRelations) {
            $this->existingRelations = $this->doctrineHelper
                ->getEntityRepository($this->priceListToWebsiteClass)
                ->findBy(['website' => $website], ['priority' => PriceListCollectionType::DEFAULT_ORDER]);
        }

        return $this->existingRelations;
    }

    /**
     * @param Website $website
     * @return null|PriceListWebsiteFallback
     */
    protected function getFallback(Website $website)
    {
        return $this->doctrineHelper
            ->getEntityRepository('OroPricingBundle:PriceListWebsiteFallback')
            ->findOneBy(['website' => $website]);
    }
}
