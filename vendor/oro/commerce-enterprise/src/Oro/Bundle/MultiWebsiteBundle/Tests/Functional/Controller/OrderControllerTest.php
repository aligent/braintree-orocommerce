<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Controller;

use Oro\Bundle\OrderBundle\Tests\Functional\Controller\OrderControllerTest as BaseOrderControllerTest;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * @dbIsolation
 */
class OrderControllerTest extends BaseOrderControllerTest
{
    /**
     * @var Website
     */
    protected $website;

    /**
     * {@inheritdoc}
     */
    public function getSubmittedData($form, $orderCustomer, $lineItems, $discountItems)
    {
        $submittedData = parent::getSubmittedData(
            $form,
            $orderCustomer,
            $lineItems,
            $discountItems
        );
        $submittedData['oro_order_type']['website'] = $this->getWebsite()->getId();

        return $submittedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedData($form, $orderCustomer, $lineItems, $discountItems)
    {
        $updatedData = parent::getUpdatedData(
            $form,
            $orderCustomer,
            $lineItems,
            $discountItems
        );
        $updatedData['oro_order_type']['website'] = $this->getWebsite()->getId();

        return $updatedData;
    }


    /**
     * @return Website
     */
    public function getWebsite()
    {
        if (!$this->website) {
            $this->website = $this->client->getContainer()->get('oro_website.manager')->getDefaultWebsite();
        }

        return $this->website;
    }
}
