<?php

namespace Oro\Bundle\LDAPBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Event\LoadIntegrationThemesEvent;

use Oro\Bundle\LDAPBundle\Provider\ChannelType;

class LoadIntegrationThemesListener
{
    const LDAP_THEME = 'OroLDAPBundle:Form:fields.html.twig';

    /**
     * @param LoadIntegrationThemesEvent $event
     */
    public function onLoad(LoadIntegrationThemesEvent $event)
    {
        $formView = $event->getFormView();
        if (!isset($formView->vars['value']) || !$formView->vars['value'] instanceof Channel) {
            return;
        }

        $channel = $formView->vars['value'];
        if ($channel->getType() !== ChannelType::TYPE) {
            return;
        }

        $event->addTheme(static::LDAP_THEME);
    }
}
