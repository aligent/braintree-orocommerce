<?php
namespace Entrepids\Bundle\BraintreeBundle\Integration;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Entity\Repository\BraintreeSettingsRepository;
use Entrepids\Bundle\BraintreeBundle\Form\Type\BraintreeSettingsType;
use Symfony\Component\HttpFoundation\ParameterBag;

class BraintreePaymentsTransport implements TransportInterface
{

    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     *
     * @param Transport $transportEntity
     */
    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
    }

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\IntegrationBundle\Provider\TransportInterface::getSettingsFormType()
     */
    public function getSettingsFormType()
    {
        return BraintreeSettingsType::class;
    }

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\IntegrationBundle\Provider\TransportInterface::getSettingsEntityFQCN()
     */
    public function getSettingsEntityFQCN()
    {
        return BraintreeSettings::class;
    }

    /**
     * (non-PHPdoc)
     * @see \Oro\Bundle\IntegrationBundle\Provider\TransportInterface::getLabel()
     */
    public function getLabel()
    {
        return 'entrepids.braintree.settings.label';
    }
}
