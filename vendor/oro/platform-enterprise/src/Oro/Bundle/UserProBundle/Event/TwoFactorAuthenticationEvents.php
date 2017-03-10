<?php

namespace Oro\Bundle\UserProBundle\Event;

final class TwoFactorAuthenticationEvents
{
    /**
     * The start event occurs when current token is replaced with Two Factor token.
     *
     * This event allows to prepare and send the authentication code to the user
     */
    const START = 'oro_userpro.security.authentication.two_factor_start';
}
