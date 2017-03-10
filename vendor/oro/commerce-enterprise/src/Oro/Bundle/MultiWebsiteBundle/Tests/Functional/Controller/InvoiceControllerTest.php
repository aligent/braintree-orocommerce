<?php

namespace Oro\Bundle\MultiWebsiteBundle\Tests\Functional\Controller;

use Oro\Bundle\InvoiceBundle\Tests\Functional\Controller\InvoiceControllerTest as BaseInvoiceControllerTest;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

/**
 * @dbIsolation
 */
class InvoiceControllerTest extends BaseInvoiceControllerTest
{
    /**
     * {@inheritdoc}
     */
    public function getSubmittedData($form, $customer, $today, $lineItems, $poNumber)
    {
        $data = parent::getSubmittedData($form, $customer, $today, $lineItems, $poNumber);

        $data['website'] = $this->getReference(LoadWebsiteData::WEBSITE1)->getId();

        return $data;
    }
}
