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
        // Nothing to do here
    }

    public function getLabel(): string
    {
        return 'aligent.braintree.settings.transport.label';
    }

    public function getSettingsFormType(): string
    {
        return BraintreeIntegrationSettingsType::class;
    }

    public function getSettingsEntityFQCN(): string
    {
        return BraintreeIntegrationSettings::class;
    }
}
