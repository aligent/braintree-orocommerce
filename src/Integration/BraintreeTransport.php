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
    public function init(Transport $transportEntity): void
    {
    }

    /**
     * Returns label for UI
     */
    public function getLabel(): string
    {
        return 'aligent.braintree.settings.transport.label';
    }

    /**
     * Returns form type name needed to setup transport
     */
    public function getSettingsFormType(): string
    {
        return BraintreeIntegrationSettingsType::class;
    }

    /**
     * Returns entity name needed to store transport settings
     */
    public function getSettingsEntityFQCN(): string
    {
        return BraintreeIntegrationSettings::class;
    }
}
