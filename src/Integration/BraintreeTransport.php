<?php
/**
 * @category  Aligent
 * @package   BraintreeBundle
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\BraintreeBundle\Integration;


use Aligent\BraintreeBundle\Entity\BraintreeIntegrationSettings;
use Aligent\BraintreeBundle\Form\Type\BraintreeIntegrationSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class BraintreeTransport implements TransportInterface
{

    /**
     * @param Transport $transportEntity
     */
    public function init(Transport $transportEntity)
    {
    }

    /**
     * Returns label for UI
     *
     * @return string
     */
    public function getLabel()
    {
        return 'aligent.braintree.settings.transport.label';
    }

    /**
     * Returns form type name needed to setup transport
     *
     * @return string
     */
    public function getSettingsFormType()
    {
        return BraintreeIntegrationSettingsType::class;
    }

    /**
     * Returns entity name needed to store transport settings
     *
     * @return string
     */
    public function getSettingsEntityFQCN()
    {
        return BraintreeIntegrationSettings::class;
    }
}