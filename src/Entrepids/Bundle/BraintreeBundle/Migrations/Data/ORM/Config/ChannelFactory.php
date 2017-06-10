<?php

namespace Entrepids\Bundle\BraintreeBundle\Migrations\Data\ORM\Config;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Entrepids\Bundle\BraintreeBundle\Entity\BraintreeSettings;
use Entrepids\Bundle\BraintreeBundle\Integration\BraintreePaymentChannelType;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChannelFactory
{
    /**
     * @var MoneyOrderChannelType
     */
    private $channelType;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param BraintreePaymentChannelType $braintreePaymentChannelType
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        BraintreePaymentChannelType $braintreePaymentChannelType,
        TranslatorInterface $translator
    ) {
        $this->channelType = $braintreePaymentChannelType;
        $this->translator = $translator;
    }

    /**
     * @param OrganizationInterface $organization
     * @param BraintreeSettings    $settings
     * @param bool                  $isEnabled
     *
     * @return Channel
     */
    public function createChannel(
        OrganizationInterface $organization,
        BraintreeSettings $settings,
        $isEnabled
    ) {
        $name = $this->getChannelTypeTranslatedLabel($this->channelType);

        $channel = new Channel();
        $channel->setType(BraintreePaymentChannelType::TYPE)
            ->setName($name)
            ->setEnabled($isEnabled)
            ->setOrganization($organization)
            ->setTransport($settings);

        return $channel;
    }

    /**
     * @param ChannelInterface $channel
     *
     * @return string
     */
    private function getChannelTypeTranslatedLabel(ChannelInterface $channel)
    {
        return $this->translator->trans($channel->getLabel());
    }
}
